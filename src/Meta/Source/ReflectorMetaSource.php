<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Source;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Message;
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
use Orisai\ObjectMapper\Rules\RuleDefinition;
use Orisai\ReflectionMeta\Reader\MetaReader;
use Orisai\ReflectionMeta\Structure\PropertyStructure;
use Orisai\ReflectionMeta\Structure\StructureBuilder;
use Orisai\ReflectionMeta\Structure\StructureFlattener;
use Orisai\ReflectionMeta\Structure\StructureGroup;
use Orisai\ReflectionMeta\Structure\StructureGrouper;
use Orisai\SourceMap\AboveReflectorSource;
use Orisai\SourceMap\ReflectorSource;
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
		$group = $this->getStructureGroup($class);

		$sources = [];
		foreach ($group->getClasses() as $structure) {
			$sources[] = $structure->getSource();
		}

		return new CompileMeta(
			$this->loadClassMeta($class, $group),
			$this->loadPropertiesMeta($class, $group),
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
	 * @param ReflectionClass<MappedObject> $rootClass
	 * @return list<ClassCompileMeta>
	 */
	private function loadClassMeta(ReflectionClass $rootClass, StructureGroup $group): array
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
					$className = $reflector->getName();
					$isRootClass = $rootClass->getName() === $className;

					$message = Message::create()
						->withContext("Resolving metadata of mapped object '{$rootClass->getName()}'.")
						->withProblem(sprintf(
							"Rule definition '%s'%s cannot be used on class, it is only allowed on properties.",
							get_class($definition),
							$isRootClass ? '' : " (used above class '$className')",
						));

					throw InvalidArgument::create()
						->withMessage($message);
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
	 * @param ReflectionClass<MappedObject> $rootClass
	 * @return list<FieldCompileMeta>
	 */
	private function loadPropertiesMeta(ReflectionClass $rootClass, StructureGroup $group): array
	{
		$resolved = [];
		foreach ($group->getGroupedProperties() as $groupedProperty) {
			$resolvedGroup = [];
			foreach ($groupedProperty as $propertyStructure) {
				$reflector = $propertyStructure->getSource()->getReflector();
				$definitions = $this->reader->readProperty($reflector, MetaDefinition::class);

				$callbacks = [];
				$docs = [];
				$modifiers = [];
				$rule = null;

				foreach ($definitions as $definition) {
					$definition = $this->checkDefinitionType($definition);

					if ($definition instanceof RuleDefinition) {
						if ($rule !== null) {
							$propertyName = $this->getRelativePropertyName($propertyStructure, $rootClass);

							$message = Message::create()
								->withContext("Resolving metadata of mapped object '{$rootClass->getName()}'.")
								->withProblem(
									"Property '$propertyName' has multiple rule definitions"
									. " (in {$this->getSourceName()}), but only one is allowed.",
								)
								->withSolution(sprintf(
									"Combine multiple with '%s' or '%s'.",
									$this->getAnyOfSourceKey(),
									$this->getAllOfSourceKey(),
								));

							throw InvalidArgument::create()
								->withMessage($message);
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
					$propertyName = $this->getRelativePropertyName($propertyStructure, $rootClass);

					$message = Message::create()
						->withContext("Resolving metadata of mapped object '{$rootClass->getName()}'.")
						->withProblem(
							"Property '$propertyName' has some mapped object definition"
							. " (in {$this->getSourceName()}), but no rule definition.",
						)
						->withSolution('Either remove the definition or add a rule definition.');

					throw InvalidArgument::create()
						->withMessage($message);
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

			$this->checkFieldInvariance($rootClass, $resolvedGroup);
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
					"Definition '%s' (subtype of '%s') should implement '%s', '%s', '%s' or '%s'.",
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
	 * @param ReflectionClass<MappedObject> $rootClass
	 * @param list<FieldCompileMeta> $resolvedGroup
	 */
	private function checkFieldInvariance(ReflectionClass $rootClass, array $resolvedGroup): void
	{
		$sourceName = $this->getSourceName();
		$previousFieldMeta = null;
		foreach ($resolvedGroup as $fieldMeta) {
			if ($previousFieldMeta !== null && !$fieldMeta->hasEqualMeta($previousFieldMeta)) {
				$name = $this->getRelativePropertyName($fieldMeta->getProperty(), $rootClass);
				$previousName = $this->getRelativePropertyName($previousFieldMeta->getProperty(), $rootClass);

				$message = Message::create()
					->withContext("Resolving metadata of mapped object '{$rootClass->getName()}'.")
					->withProblem(
						"Definition in $sourceName of property '$name' differs from definition in $sourceName"
						. " of property '$previousName'.",
					)
					->withSolution("Don't override metadata of properties in child classes.");

				throw InvalidArgument::create()
					->withMessage($message);
			}

			$previousFieldMeta = $fieldMeta;
		}
	}

	/**
	 * @param ReflectionClass<MappedObject> $rootClass
	 */
	private function getRelativePropertyName(PropertyStructure $propertyStructure, ReflectionClass $rootClass): string
	{
		$property = $propertyStructure->getSource()->getReflector();
		$class = $property->getDeclaringClass();

		if ($class->getName() === $rootClass->getName()) {
			return '$' . $property->getName();
		}

		return $class->getName() . '->$' . $property->getName();
	}

	/**
	 * @template T of ReflectorSource
	 * @param T $source
	 * @return AboveReflectorSource<T>
	 */
	abstract protected function wrapSource(ReflectorSource $source): AboveReflectorSource;

	abstract protected function getSourceName(): string;

	abstract protected function getAnyOfSourceKey(): string;

	abstract protected function getAllOfSourceKey(): string;

}
