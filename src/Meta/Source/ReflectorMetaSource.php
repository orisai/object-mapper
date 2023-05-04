<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Source;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Callbacks\CallableAttribute;
use Orisai\ObjectMapper\Docs\DocumentationAttribute;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\BaseAttribute;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\FieldCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ModifierCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Meta\DocMeta;
use Orisai\ObjectMapper\Modifiers\ModifierAttribute;
use Orisai\ObjectMapper\Rules\AllOf;
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\RuleAttribute;
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
			$attributes = $this->reader->readClass($reflector, BaseAttribute::class);

			$callbacks = [];
			$docs = [];
			$modifiers = [];

			foreach ($attributes as $attribute) {
				$attribute = $this->checkAnnotationType($attribute);

				if ($attribute instanceof RuleAttribute) {
					throw InvalidArgument::create()
						->withMessage(sprintf(
							'Rule attribute %s (subtype of %s) cannot be used on class, only properties are allowed',
							get_class($attribute),
							RuleAttribute::class,
						));
				}

				if ($attribute instanceof CallableAttribute) {
					$callbacks[] = new CallbackCompileMeta(
						$attribute->getType(),
						$attribute->getArgs(),
					);
				} elseif ($attribute instanceof DocumentationAttribute) {
					$docs[] = new DocMeta(
						$attribute->getType(),
						$attribute->getArgs(),
					);
				} else {
					$modifiers[] = new ModifierCompileMeta(
						$attribute->getType(),
						$attribute->getArgs(),
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
			$attributes = $this->reader->readProperty($reflector, BaseAttribute::class);

			$class = $propertyStructure->getContextClass();
			$property = $class->getProperty($reflector->getName());

			$callbacks = [];
			$docs = [];
			$modifiers = [];
			$rule = null;

			foreach ($attributes as $attribute) {
				$attribute = $this->checkAnnotationType($attribute);

				if ($attribute instanceof RuleAttribute) {
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
						$attribute->getType(),
						$attribute->getArgs(),
					);
				} elseif ($attribute instanceof CallableAttribute) {
					$callbacks[] = new CallbackCompileMeta(
						$attribute->getType(),
						$attribute->getArgs(),
					);
				} elseif ($attribute instanceof DocumentationAttribute) {
					$docs[] = new DocMeta(
						$attribute->getType(),
						$attribute->getArgs(),
					);
				} else {
					$modifiers[] = new ModifierCompileMeta(
						$attribute->getType(),
						$attribute->getArgs(),
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
	 * @return CallableAttribute|DocumentationAttribute|ModifierAttribute|RuleAttribute
	 */
	private function checkAnnotationType(BaseAttribute $annotation): BaseAttribute
	{
		if (
			!$annotation instanceof CallableAttribute
			&& !$annotation instanceof DocumentationAttribute
			&& !$annotation instanceof ModifierAttribute
			&& !$annotation instanceof RuleAttribute
		) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Annotation %s (subtype of %s) should implement %s, %s %s or %s',
					get_class($annotation),
					BaseAttribute::class,
					CallableAttribute::class,
					DocumentationAttribute::class,
					ModifierAttribute::class,
					RuleAttribute::class,
				));
		}

		return $annotation;
	}

}
