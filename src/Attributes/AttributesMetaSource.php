<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Attributes\Callbacks\CallableAttribute;
use Orisai\ObjectMapper\Attributes\Docs\DocumentationAttribute;
use Orisai\ObjectMapper\Attributes\Expect\RuleAttribute;
use Orisai\ObjectMapper\Attributes\Modifiers\ModifierAttribute;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ModifierCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\PropertyCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Meta\DocMeta;
use Orisai\ObjectMapper\Meta\MetaSource;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use function array_merge;
use function get_class;
use function is_a;
use function sprintf;
use const PHP_VERSION_ID;

final class AttributesMetaSource implements MetaSource
{

	private Reader $reader;

	public function __construct(?Reader $reader = null)
	{
		$this->reader = $reader ?? new AnnotationReader();
	}

	public function load(ReflectionClass $class): CompileMeta
	{
		return new CompileMeta(
			$this->loadClassMeta($class),
			$this->loadPropertiesMeta($class),
		);
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	private function loadClassMeta(ReflectionClass $class): ClassCompileMeta
	{
		$callbacks = [];
		$docs = [];
		$modifiers = [];

		foreach ($this->getClassAttributes($class) as $annotation) {
			if (!$annotation instanceof BaseAttribute) {
				continue;
			}

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
	 * @param ReflectionClass<MappedObject> $class
	 * @return array<string, PropertyCompileMeta>
	 */
	private function loadPropertiesMeta(ReflectionClass $class): array
	{
		$properties = [];

		foreach ($class->getProperties() as $property) {
			$callbacks = [];
			$docs = [];
			$modifiers = [];
			$rule = null;

			foreach ($this->getPropertyAttributes($property) as $annotation) {
				if (!$annotation instanceof BaseAttribute) {
					continue;
				}

				$annotation = $this->checkAnnotationType($annotation);

				if ($annotation instanceof RuleAttribute) {
					if ($rule !== null) {
						throw InvalidArgument::create()
							->withMessage(sprintf(
								'Mapped property %s::$%s has multiple expectation annotations, while only one is allowed. ' .
								'Combine multiple with %s or %s',
								$class->getName(),
								$property->getName(),
								Expect\AnyOf::class,
								Expect\AllOf::class,
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

			$properties[$property->getName()] = new PropertyCompileMeta(
				$callbacks,
				$docs,
				$modifiers,
				$rule,
			);
		}

		return $properties;
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
	 * @param ReflectionClass<MappedObject> $class
	 * @return array<int, object>
	 */
	private function getClassAttributes(ReflectionClass $class): array
	{
		$attributesBySource = [];
		if (PHP_VERSION_ID >= 8_01_00) {
			$attributesBySource[] = $this->reflectionAttributesToInstances(
				$class->getAttributes(),
			);
		}

		$attributesBySource[] = $this->reader->getClassAnnotations($class);

		return array_merge(...$attributesBySource);
	}

	/**
	 * @return array<int, object>
	 */
	private function getPropertyAttributes(ReflectionProperty $property): array
	{
		$attributesBySource = [];
		if (PHP_VERSION_ID >= 8_01_00) {
			$attributesBySource[] = $this->reflectionAttributesToInstances(
				$property->getAttributes(),
			);
		}

		$attributesBySource[] = $this->reader->getPropertyAnnotations($property);

		return array_merge(...$attributesBySource);
	}

	/**
	 * @param array<int, ReflectionAttribute<object>> $reflectionAttributes
	 * @return array<int, object>
	 */
	private function reflectionAttributesToInstances(array $reflectionAttributes): array
	{
		$attributes = [];
		foreach ($reflectionAttributes as $attribute) {
			if (!is_a($attribute->getName(), BaseAttribute::class, true)) {
				continue;
			}

			$attributes[] = $attribute->newInstance();
		}

		return $attributes;
	}

}
