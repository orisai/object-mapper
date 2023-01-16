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
use function array_merge;
use function get_class;
use function sprintf;

abstract class BaseMetaSource implements MetaSource
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
			$this->loadPropertiesMeta($metas),
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
	 * @return list<ClassCompileMeta>
	 */
	private function loadClassMeta(array $metas): array
	{
		$callbacks = [];
		$docs = [];
		$modifiers = [];

		$resolved = [];
		foreach ($metas as $meta) {
			foreach ($meta->getAttributes() as $attribute) {
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

			$class = $meta->getSource()->getTarget()->getReflector();
			$resolved[] = new ClassCompileMeta($callbacks, $docs, $modifiers, $class);
		}

		return $resolved;
	}

	/**
	 * @param list<ClassMeta<BaseAttribute>> $metas
	 * @return list<FieldCompileMeta>
	 */
	private function loadPropertiesMeta(array $metas): array
	{
		$fields = [];

		foreach ($metas as $meta) {
			foreach ($meta->getProperties() as $propertyMeta) {
				$property = $propertyMeta->getSource()->getTarget()->getReflector();
				$class = $property->getDeclaringClass();

				$callbacks = [];
				$docs = [];
				$modifiers = [];
				$rule = null;

				foreach ($propertyMeta->getAttributes() as $attribute) {
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
