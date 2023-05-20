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
use Orisai\ReflectionMeta\Structure\StructureList;
use ReflectionClass;
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
		$structures = $this->getStructureList($class);

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
	private function getStructureList(ReflectionClass $class): StructureList
	{
		return StructureFlattener::flatten(
			StructureBuilder::build($class),
		);
	}

	/**
	 * @return list<ClassCompileMeta>
	 */
	private function loadClassMeta(StructureList $structures): array
	{
		$resolved = [];
		foreach ($structures->getClasses() as $class) {
			$reflector = $class->getSource()->getReflector();
			$definitions = $this->reader->readClass($reflector, MetaDefinition::class);

			$callbacks = [];
			$docs = [];
			$modifiers = [];

			foreach ($definitions as $definition) {
				$definition = $this->checkAnnotationType($definition);

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

			$resolved[] = new ClassCompileMeta($callbacks, $docs, $modifiers, $class->getContextClass());
		}

		return $resolved;
	}

	/**
	 * @return list<FieldCompileMeta>
	 */
	private function loadPropertiesMeta(StructureList $structures): array
	{
		$fields = [];

		foreach ($structures->getProperties() as $propertyStructure) {
			$reflector = $propertyStructure->getSource()->getReflector();
			$definitions = $this->reader->readProperty($reflector, MetaDefinition::class);

			$class = $propertyStructure->getContextClass();
			$property = $class->getProperty($reflector->getName());

			$callbacks = [];
			$docs = [];
			$modifiers = [];
			$rule = null;

			foreach ($definitions as $definition) {
				$definition = $this->checkAnnotationType($definition);

				if ($definition instanceof RuleDefinition) {
					if ($rule !== null) {
						throw InvalidArgument::create()
							->withMessage(sprintf(
								'Mapped property %s::$%s has multiple expectation annotations, while only one is allowed. ' .
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
						"Property {$class->getName()}::\${$property->getName()} has mapped object annotation, " .
						'but no rule annotation.',
					);
			}

			$fields[] = new FieldCompileMeta(
				$callbacks,
				$docs,
				$modifiers,
				$rule,
				$property,
			);
		}

		return $fields;
	}

	/**
	 * @return CallbackDefinition|DocDefinition|ModifierDefinition|RuleDefinition
	 */
	private function checkAnnotationType(MetaDefinition $annotation): MetaDefinition
	{
		if (
			!$annotation instanceof CallbackDefinition
			&& !$annotation instanceof DocDefinition
			&& !$annotation instanceof ModifierDefinition
			&& !$annotation instanceof RuleDefinition
		) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Annotation %s (subtype of %s) should implement %s, %s %s or %s',
					get_class($annotation),
					MetaDefinition::class,
					CallbackDefinition::class,
					DocDefinition::class,
					ModifierDefinition::class,
					RuleDefinition::class,
				));
		}

		return $annotation;
	}

}
