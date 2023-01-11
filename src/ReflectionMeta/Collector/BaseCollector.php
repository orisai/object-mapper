<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\ReflectionMeta\Collector;

use Orisai\ObjectMapper\ReflectionMeta\Meta\ClassConstantMeta;
use Orisai\ObjectMapper\ReflectionMeta\Meta\ClassMeta;
use Orisai\ObjectMapper\ReflectionMeta\Meta\MethodMeta;
use Orisai\ObjectMapper\ReflectionMeta\Meta\ParameterMeta;
use Orisai\ObjectMapper\ReflectionMeta\Meta\PropertyMeta;
use Orisai\SourceMap\AboveReflectorSource;
use Orisai\SourceMap\ClassConstantSource;
use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\MethodSource;
use Orisai\SourceMap\ParameterSource;
use Orisai\SourceMap\PropertySource;
use Orisai\SourceMap\ReflectorSource;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use function array_reverse;
use function array_values;
use function ksort;

abstract class BaseCollector implements Collector
{

	/**
	 * @template T of object
	 * @param ReflectionClass<object> $class
	 * @param class-string<T>         $attributeClass
	 * @return list<T>
	 */
	abstract protected function getClassReflectorAttributes(ReflectionClass $class, string $attributeClass): array;

	/**
	 * @template T of object
	 * @param class-string<T> $attributeClass
	 * @return list<T>
	 */
	abstract protected function getPropertyReflectorAttributes(
		ReflectionProperty $property,
		string $attributeClass
	): array;

	/**
	 * @template T of object
	 * @param class-string<T> $attributeClass
	 * @return list<T>
	 */
	abstract protected function getMethodReflectorAttributes(ReflectionMethod $method, string $attributeClass): array;

	/**
	 * @template T of object
	 * @param class-string<T> $attributeClass
	 * @return list<T>
	 */
	abstract protected function getClassConstantReflectorAttributes(
		ReflectionClassConstant $constant,
		string $attributeClass
	): array;

	/**
	 * @template T of object
	 * @param class-string<T> $attributeClass
	 * @return list<T>
	 */
	abstract protected function getParameterReflectorAttributes(
		ReflectionParameter $parameter,
		string $attributeClass
	): array;

	/**
	 * @template S of ReflectorSource
	 * @param S $target
	 * @return AboveReflectorSource<S>
	 */
	abstract protected function createAboveReflectorSource(ReflectorSource $target): AboveReflectorSource;

	public function collect(ReflectionClass $collectedClass, string $attributeClass): array
	{
		$collected = [];
		foreach ($this->getClassReflectors($collectedClass) as $class) {
			$collected[] = $this->createClassMeta($class, $attributeClass);

			foreach ($this->sortClassReflectors($class->getInterfaces()) as $interface) {
				$collected[] = $this->createClassMeta($interface, $attributeClass);
			}

			foreach ($this->sortClassReflectors($class->getTraits()) as $trait) {
				$collected[] = $this->createClassMeta($trait, $attributeClass);
			}
		}

		return $collected;
	}

	/**
	 * @template T of object
	 * @param ReflectionClass<T> $reflector
	 * @return non-empty-list<ReflectionClass<object>>
	 */
	private function getClassReflectors(ReflectionClass $reflector): array
	{
		$list = [];
		while ($reflector !== false) {
			$list[] = $reflector;
			$reflector = $reflector->getParentClass();
		}

		// Sorted from first parent to last child
		return array_reverse($list);
	}

	/**
	 * @template T of object
	 * @param array<ReflectionClass<T>> $reflectors
	 * @return list<ReflectionClass<object>>
	 */
	private function sortClassReflectors(array $reflectors): array
	{
		$sorted = [];
		foreach ($reflectors as $reflector) {
			$sorted[$reflector->getName()] = $reflector;
		}

		ksort($sorted);

		return array_values($sorted);
	}

	/**
	 * @template T of object
	 * @param ReflectionClass<object> $class
	 * @param class-string<T>         $attributeClass
	 * @return ClassMeta<T>
	 */
	private function createClassMeta(ReflectionClass $class, string $attributeClass): ClassMeta
	{
		$constants = [];
		foreach ($class->getReflectionConstants() as $constant) {
			$constants[] = new ClassConstantMeta(
				$this->createAboveReflectorSource(new ClassConstantSource($constant)),
				$this->getClassConstantReflectorAttributes($constant, $attributeClass),
			);
		}

		$properties = [];
		foreach ($class->getProperties() as $property) {
			$properties[] = new PropertyMeta(
				$this->createAboveReflectorSource(new PropertySource($property)),
				$this->getPropertyReflectorAttributes($property, $attributeClass),
			);
		}

		$methods = [];
		foreach ($class->getMethods() as $method) {
			$parameters = [];
			foreach ($method->getParameters() as $parameter) {
				$parameters[] = new ParameterMeta(
					$this->createAboveReflectorSource(new ParameterSource($parameter)),
					$this->getParameterReflectorAttributes($parameter, $attributeClass),
				);
			}

			$methods[] = new MethodMeta(
				$this->createAboveReflectorSource(new MethodSource($method)),
				$this->getMethodReflectorAttributes($method, $attributeClass),
				$parameters,
			);
		}

		return new ClassMeta(
			$this->createAboveReflectorSource(new ClassSource($class)),
			$this->getClassReflectorAttributes($class, $attributeClass),
			$constants,
			$properties,
			$methods,
		);
	}

}
