<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Source;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Callbacks\CallbackDefinition;
use Orisai\ObjectMapper\Docs\DocDefinition;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\FieldCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ModifierCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Meta\MetaDefinition;
use Orisai\ObjectMapper\Meta\Shared\DocMeta;
use Orisai\ObjectMapper\Modifiers\ModifierDefinition;
use Orisai\ObjectMapper\Rules\AllOf;
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\RuleDefinition;
use Orisai\ReflectionMeta\Reader\MetaReader;
use Orisai\ReflectionMeta\Structure\StructureBuilder;
use Orisai\ReflectionMeta\Structure\StructureFlattener;
use Orisai\ReflectionMeta\Structure\StructureGroup;
use Orisai\ReflectionMeta\Structure\StructureGrouper;
use ReflectionClass;
use function array_key_first;
use function get_class;
use function sprintf;

abstract class ReflectorMetaSource implements MetaSource
{

	private MetaReader $reader;

	public function __construct(MetaReader $reader)
	{
		$this->reader = $reader;
	}

	public function load(ReflectionClass $class): CompileMeta
	{
		$structures = $this->getStructureGroup($class);

		$sources = [];
		foreach ($structures->getClasses() as $structure) {
			$sources[] = $structure->getSource();
		}

		return new CompileMeta(
			$this->loadClassMeta($structures),
			$this->loadPropertiesMeta($structures),
			$sources,
		);
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	private function getStructureGroup(ReflectionClass $class): StructureGroup
	{
		return StructureGrouper::group(
			StructureFlattener::flatten(
				StructureBuilder::build($class),
			),
		);
	}

	/**
	 * @return list<ClassCompileMeta>
	 */
	private function loadClassMeta(StructureGroup $group): array
	{
		$resolved = [];
		foreach ($group->getClasses() as $class) {
			$reflector = $class->getSource()->getReflector();
			$definitions = $this->reader->readClass($reflector, MetaDefinition::class);

			$callbacks = [];
			$docs = [];
			$modifiers = [];

			foreach ($definitions as $definition) {
				$definition = $this->checkDefinitionType($definition);

				if ($definition instanceof RuleDefinition) {
					throw InvalidArgument::create()
						->withMessage(sprintf(
							'Rule definition %s (subtype of %s) cannot be used on class, only properties are allowed',
							get_class($definition),
							RuleDefinition::class,
						));
				}

				if ($definition instanceof CallbackDefinition) {
					$callbacks[] = new CallbackCompileMeta(
						$definition->getType(),
						$definition->getArgs(),
					);
				} elseif ($definition instanceof DocDefinition) {
					$docs[] = new DocMeta(
						$definition->getType(),
						$definition->getArgs(),
					);
				} else {
					$modifiers[] = new ModifierCompileMeta(
						$definition->getType(),
						$definition->getArgs(),
					);
				}
			}

			if ($callbacks === [] && $docs === [] && $modifiers === []) {
				continue;
			}

			$resolved[] = new ClassCompileMeta($callbacks, $docs, $modifiers, $class);
		}

		return $resolved;
	}

	/**
	 * @return list<FieldCompileMeta>
	 */
	private function loadPropertiesMeta(StructureGroup $group): array
	{
		$resolved = [];
		foreach ($group->getGroupedProperties() as $groupedProperty) {
			$resolvedGroup = [];
			foreach ($groupedProperty as $propertyStructure) {
				$reflector = $propertyStructure->getSource()->getReflector();
				$definitions = $this->reader->readProperty($reflector, MetaDefinition::class);

				$property = $propertyStructure->getContextReflector();
				$class = $property->getDeclaringClass();

				$callbacks = [];
				$docs = [];
				$modifiers = [];
				$rule = null;

				foreach ($definitions as $definition) {
					$definition = $this->checkDefinitionType($definition);

					if ($definition instanceof RuleDefinition) {
						if ($rule !== null) {
							throw InvalidArgument::create()
								->withMessage(sprintf(
									'Mapped property %s::$%s has multiple expectation definitions, while only one is allowed. ' .
									'Combine multiple with %s or %s',
									$class->getName(),
									$property->getName(),
									AnyOf::class,
									AllOf::class,
								));
						}

						$rule = new RuleCompileMeta(
							$definition->getType(),
							$definition->getArgs(),
						);
					} elseif ($definition instanceof CallbackDefinition) {
						$callbacks[] = new CallbackCompileMeta(
							$definition->getType(),
							$definition->getArgs(),
						);
					} elseif ($definition instanceof DocDefinition) {
						$docs[] = new DocMeta(
							$definition->getType(),
							$definition->getArgs(),
						);
					} else {
						$modifiers[] = new ModifierCompileMeta(
							$definition->getType(),
							$definition->getArgs(),
						);
					}
				}

				if ($rule === null && $callbacks === [] && $docs === [] && $modifiers === []) {
					continue;
				}

				if ($rule === null) {
					throw InvalidArgument::create()
						->withMessage(
							"Property {$class->getName()}::\${$property->getName()} has mapped object definition, " .
							'but no rule definition.',
						);
				}

				$resolvedGroup[] = new FieldCompileMeta(
					$callbacks,
					$docs,
					$modifiers,
					$rule,
					$propertyStructure,
				);
			}

			if ($resolvedGroup === []) {
				continue;
			}

			$this->checkFieldInvariance($resolvedGroup);
			$resolved[] = $resolvedGroup[array_key_first($resolvedGroup)];
		}

		return $resolved;
	}

	/**
	 * @return CallbackDefinition|DocDefinition|ModifierDefinition|RuleDefinition
	 */
	private function checkDefinitionType(MetaDefinition $definition): MetaDefinition
	{
		if (
			!$definition instanceof CallbackDefinition
			&& !$definition instanceof DocDefinition
			&& !$definition instanceof ModifierDefinition
			&& !$definition instanceof RuleDefinition
		) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Definition %s (subtype of %s) should implement %s, %s %s or %s',
					get_class($definition),
					MetaDefinition::class,
					CallbackDefinition::class,
					DocDefinition::class,
					ModifierDefinition::class,
					RuleDefinition::class,
				));
		}

		return $definition;
	}

	/**
	 * @param list<FieldCompileMeta> $resolvedGroup
	 */
	private function checkFieldInvariance(array $resolvedGroup): void
	{
		$previousFieldMeta = null;
		foreach ($resolvedGroup as $fieldMeta) {
			if ($previousFieldMeta !== null && !$fieldMeta->hasEqualMeta($previousFieldMeta)) {
				throw InvalidArgument::create()
					->withMessage(
						"Definition of property '{$fieldMeta->getClass()->getContextReflector()->getName()}"
						. "::\${$fieldMeta->getProperty()->getContextReflector()->getName()}'"
						. " can't be changed but it differs from definition in '{$previousFieldMeta->getClass()->getContextReflector()->getName()}'.",
					);
			}

			$previousFieldMeta = $fieldMeta;
		}
	}

}
