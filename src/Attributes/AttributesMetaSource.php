<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Attributes\Callbacks\CallableAttribute;
use Orisai\ObjectMapper\Attributes\Docs\DocumentationAttribute;
use Orisai\ObjectMapper\Attributes\Expect\AllOf;
use Orisai\ObjectMapper\Attributes\Expect\AnyOf;
use Orisai\ObjectMapper\Attributes\Expect\RuleAttribute;
use Orisai\ObjectMapper\Attributes\Modifiers\ModifierAttribute;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\FieldCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ModifierCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Meta\DocMeta;
use Orisai\ObjectMapper\Meta\MetaSource;
use Orisai\ObjectMapper\ReflectionMeta\Collector\Collector;
use Orisai\ObjectMapper\ReflectionMeta\Meta\ClassMeta;
use Orisai\ObjectMapper\ReflectionMeta\Meta\HierarchicClassMeta;
use ReflectionClass;
use ReflectionProperty;
use function array_merge;
use function get_class;
use function sprintf;

final class AttributesMetaSource implements MetaSource
{

	private Collector $collector;

	public function __construct(Collector $collector)
	{
		$this->collector = $collector;
	}

	public function load(ReflectionClass $class): CompileMeta
	{
		$metas = $this->getMetas($class);

		$sources = [];
		foreach ($metas as $meta) {
			$sources[] = $meta->getSource()->getTarget();
		}

		return new CompileMeta(
			$this->loadClassMeta($metas),
			$this->loadPropertiesMeta($class, $metas),
			$sources,
		);
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 * @return list<ClassMeta<BaseAttribute>>
	 */
	private function getMetas(ReflectionClass $class): array
	{
		return $this->hierarchicToFlatClassMeta(
			$this->collector->collect($class, BaseAttribute::class),
		);
	}

	/**
	 * @template T of object
	 * @param HierarchicClassMeta<T> $meta
	 * @return list<ClassMeta<T>>
	 */
	private function hierarchicToFlatClassMeta(HierarchicClassMeta $meta): array
	{
		$metasBySource = [];

		$parent = $meta->getParent();
		if ($parent !== null) {
			$metasBySource[] = $this->hierarchicToFlatClassMeta($parent);
		}

		foreach ($meta->getInterfaces() as $interface) {
			$metasBySource[] = $this->hierarchicToFlatClassMeta($interface);
		}

		foreach ($meta->getTraits() as $trait) {
			$metasBySource[] = $this->hierarchicToFlatClassMeta($trait);
		}

		$metasBySource[][] = new ClassMeta(
			$meta->getSource(),
			$meta->getAttributes(),
			$meta->getConstants(),
			$meta->getProperties(),
			$meta->getMethods(),
		);

		return array_merge(...$metasBySource);
	}

	/**
	 * @param list<ClassMeta<BaseAttribute>> $metas
	 */
	private function loadClassMeta(array $metas): ClassCompileMeta
	{
		$callbacks = [];
		$docs = [];
		$modifiers = [];

		foreach ($this->getClassAttributes($metas) as $annotation) {
			$annotation = $this->checkAnnotationType($annotation);

			if ($annotation instanceof RuleAttribute) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Rule annotation %s (subtype of %s) cannot be used on class, only properties are allowed',
						get_class($annotation),
						RuleAttribute::class,
					));
			}

			if ($annotation instanceof CallableAttribute) {
				$callbacks[] = new CallbackCompileMeta(
					$annotation->getType(),
					$annotation->getArgs(),
				);
			} elseif ($annotation instanceof DocumentationAttribute) {
				$docs[] = new DocMeta(
					$annotation->getType(),
					$annotation->getArgs(),
				);
			} else {
				$modifiers[] = new ModifierCompileMeta(
					$annotation->getType(),
					$annotation->getArgs(),
				);
			}
		}

		return new ClassCompileMeta($callbacks, $docs, $modifiers);
	}

	/**
	 * @param ReflectionClass<MappedObject>  $class
	 * @param list<ClassMeta<BaseAttribute>> $metas
	 * @return array<string, FieldCompileMeta>
	 */
	private function loadPropertiesMeta(ReflectionClass $class, array $metas): array
	{
		$fields = [];

		foreach ($class->getProperties() as $property) {
			$callbacks = [];
			$docs = [];
			$modifiers = [];
			$rule = null;

			foreach ($this->getPropertyAttributes($property, $metas) as $annotation) {
				$annotation = $this->checkAnnotationType($annotation);

				if ($annotation instanceof RuleAttribute) {
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
						$annotation->getType(),
						$annotation->getArgs(),
					);
				} elseif ($annotation instanceof CallableAttribute) {
					$callbacks[] = new CallbackCompileMeta(
						$annotation->getType(),
						$annotation->getArgs(),
					);
				} elseif ($annotation instanceof DocumentationAttribute) {
					$docs[] = new DocMeta(
						$annotation->getType(),
						$annotation->getArgs(),
					);
				} else {
					$modifiers[] = new ModifierCompileMeta(
						$annotation->getType(),
						$annotation->getArgs(),
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

			$fields[$property->getName()] = new FieldCompileMeta(
				$callbacks,
				$docs,
				$modifiers,
				$rule,
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

	/**
	 * @param list<ClassMeta<BaseAttribute>> $metas
	 * @return list<BaseAttribute>
	 */
	private function getClassAttributes(array $metas): array
	{
		$attributes = [];
		foreach ($metas as $meta) {
			foreach ($meta->getAttributes() as $attribute) {
				$attributes[] = $attribute;
			}
		}

		return $attributes;
	}

	/**
	 * @param list<ClassMeta<BaseAttribute>> $metas
	 * @return list<BaseAttribute>
	 */
	private function getPropertyAttributes(ReflectionProperty $reflector, array $metas): array
	{
		$attributes = [];
		foreach ($metas as $meta) {
			foreach ($meta->getProperties() as $property) {
				$propertyReflector = $property->getSource()->getTarget()->getReflector();

				if ($reflector->getName() !== $propertyReflector->getName()) {
					continue;
				}

				foreach ($property->getAttributes() as $attribute) {
					$attributes[] = $attribute;
				}
			}
		}

		return $attributes;
	}

}
