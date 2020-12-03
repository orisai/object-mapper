<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Annotation\Callback\CallableAnnotation;
use Orisai\ObjectMapper\Annotation\Docs\DocumentationAnnotation;
use Orisai\ObjectMapper\Annotation\Expect\RuleAnnotation;
use Orisai\ObjectMapper\Annotation\Modifiers\ModifierAnnotation;
use Orisai\ObjectMapper\Exception\InvalidAnnotation;
use Orisai\ObjectMapper\Meta\MetaSource;
use Orisai\ObjectMapper\ValueObject;
use ReflectionClass;
use function get_class;
use function sprintf;

final class AnnotationMetaSource implements MetaSource
{

	private Reader $reader;

	public function __construct(?Reader $reader = null)
	{
		$this->reader = $reader ?? new AnnotationReader();
	}

	/**
	 * @return array<mixed>
	 */
	public function load(ReflectionClass $class): array
	{
		return [
			self::LOCATION_CLASS => $this->loadClassMeta($class),
			self::LOCATION_PROPERTIES => $this->loadPropertiesMeta($class),
		];
	}

	/**
	 * @param ReflectionClass<ValueObject> $class
	 * @return array<mixed>
	 */
	private function loadClassMeta(ReflectionClass $class): array
	{
		$classMeta = [];

		foreach ($this->reader->getClassAnnotations($class) as $annotation) {
			if (!$annotation instanceof BaseAnnotation) {
				continue;
			}

			$annotation = $this->checkAnnotationType($annotation);

			if ($annotation instanceof RuleAnnotation) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Rule annotation %s (subtype of %s) cannot be used on class, only properties are allowed',
						get_class($annotation),
						RuleAnnotation::class,
					));
			}

			$classMeta[$annotation::ANNOTATION_TYPE][] = AnnotationMetaExtractor::extract($annotation);
		}

		return $classMeta;
	}

	/**
	 * @param ReflectionClass<ValueObject> $class
	 * @return array<mixed>
	 */
	private function loadPropertiesMeta(ReflectionClass $class): array
	{
		$propertiesMeta = [];

		foreach ($class->getProperties() as $property) {
			$propertyName = $property->getName();
			$propertyHasValidationRule = false;

			foreach ($this->reader->getPropertyAnnotations($property) as $annotation) {
				if (!$annotation instanceof BaseAnnotation) {
					continue;
				}

				$annotation = $this->checkAnnotationType($annotation);

				if ($annotation instanceof RuleAnnotation) {
					if ($propertyHasValidationRule) {
						throw InvalidArgument::create()
							->withMessage(sprintf(
								sprintf(
									'Mapped property %s::$%s has multiple expectation annotations, while only one is allowed. Combine multiple with %s or %s',
									$class->getName(),
									$property->getName(),
									Expect\AnyOf::class,
									Expect\AllOf::class,
								),
							));
					}

					$propertyHasValidationRule = true;
					$propertiesMeta[$propertyName][$annotation::ANNOTATION_TYPE] = AnnotationMetaExtractor::extract(
						$annotation,
					);
				} else {
					$propertiesMeta[$propertyName][$annotation::ANNOTATION_TYPE][] = AnnotationMetaExtractor::extract(
						$annotation,
					);
				}
			}
		}

		return $propertiesMeta;
	}

	/**
	 * @return CallableAnnotation|DocumentationAnnotation|ModifierAnnotation|RuleAnnotation
	 */
	private function checkAnnotationType(BaseAnnotation $annotation): BaseAnnotation
	{
		if (
			!$annotation instanceof CallableAnnotation
			&& !$annotation instanceof DocumentationAnnotation
			&& !$annotation instanceof ModifierAnnotation
			&& !$annotation instanceof RuleAnnotation
		) {
			throw InvalidAnnotation::create()
				->withMessage(sprintf(
					'Annotation %s (subtype of %s) should implement %s, %s %s or %s',
					get_class($annotation),
					BaseAnnotation::class,
					CallableAnnotation::class,
					DocumentationAnnotation::class,
					ModifierAnnotation::class,
					RuleAnnotation::class,
				));
		}

		return $annotation;
	}

}
