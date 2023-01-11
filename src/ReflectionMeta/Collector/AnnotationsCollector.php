<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\ReflectionMeta\Collector;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\SourceMap\AboveReflectorSource;
use Orisai\SourceMap\AnnotationSource;
use Orisai\SourceMap\EmptyAboveReflectorSource;
use Orisai\SourceMap\ReflectorSource;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use function class_exists;
use function is_a;
use function method_exists;

final class AnnotationsCollector extends BaseCollector
{

	private Reader $reader;

	public function __construct(?Reader $reader = null)
	{
		if ($reader === null) {
			if (!class_exists(AnnotationReader::class)) {
				throw InvalidState::create()
					->withMessage('doctrine/annotations dependency is required');
			}

			$reader = new AnnotationReader();
		}

		$this->reader = $reader;
	}

	protected function getClassReflectorAttributes(ReflectionClass $class, string $attributeClass): array
	{
		return $this->filterInstances(
			$this->reader->getClassAnnotations($class),
			$attributeClass,
		);
	}

	protected function getPropertyReflectorAttributes(ReflectionProperty $property, string $attributeClass): array
	{
		return $this->filterInstances(
			$this->reader->getPropertyAnnotations($property),
			$attributeClass,
		);
	}

	protected function getMethodReflectorAttributes(ReflectionMethod $method, string $attributeClass): array
	{
		return $this->filterInstances(
			$this->reader->getMethodAnnotations($method),
			$attributeClass,
		);
	}

	protected function getClassConstantReflectorAttributes(
		ReflectionClassConstant $constant,
		string $attributeClass
	): array
	{
		return [];
	}

	protected function getParameterReflectorAttributes(ReflectionParameter $parameter, string $attributeClass): array
	{
		return [];
	}

	protected function createAboveReflectorSource(ReflectorSource $target): AboveReflectorSource
	{
		$reflector = $target->getReflector();

		if (!method_exists($reflector, 'getDocComment') || $reflector->getDocComment() === false) {
			return new EmptyAboveReflectorSource($target);
		}

		return new AnnotationSource($target);
	}

	/**
	 * @template T of object
	 * @param array<object>   $instances
	 * @param class-string<T> $attributeClass
	 * @return list<T>
	 */
	private function filterInstances(array $instances, string $attributeClass): array
	{
		$attributes = [];
		foreach ($instances as $instance) {
			if (!is_a($instance, $attributeClass)) {
				continue;
			}

			$attributes[] = $instance;
		}

		return $attributes;
	}

}
