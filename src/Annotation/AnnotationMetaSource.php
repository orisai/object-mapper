<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Annotation\Callback\CallableAnnotation;
use Orisai\ObjectMapper\Annotation\Docs\DocumentationAnnotation;
use Orisai\ObjectMapper\Annotation\Expect\RuleAnnotation;
use Orisai\ObjectMapper\Annotation\Modifiers\ModifierAnnotation;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ModifierCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\PropertyCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Meta\DocMeta;
use Orisai\ObjectMapper\Meta\MetaSource;
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

			if ($annotation instanceof CallableAnnotation) {
				$callbacks[] = new CallbackCompileMeta(
					$annotation->getType(),
					$annotation->getArgs(),
				);
			} elseif ($annotation instanceof DocumentationAnnotation) {
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

			foreach ($this->reader->getPropertyAnnotations($property) as $annotation) {
				if (!$annotation instanceof BaseAnnotation) {
					continue;
				}

				$annotation = $this->checkAnnotationType($annotation);

				if ($annotation instanceof RuleAnnotation) {
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
				} elseif ($annotation instanceof CallableAnnotation) {
					$callbacks[] = new CallbackCompileMeta(
						$annotation->getType(),
						$annotation->getArgs(),
					);
				} elseif ($annotation instanceof DocumentationAnnotation) {
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
			throw InvalidArgument::create()
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
