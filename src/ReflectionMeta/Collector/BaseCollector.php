<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\ReflectionMeta\Collector;

use Orisai\ObjectMapper\ReflectionMeta\Meta\ClassConstantMeta;
use Orisai\ObjectMapper\ReflectionMeta\Meta\HierarchicClassMeta;
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

abstract class BaseCollector implements Collector
{

	public function collect(ReflectionClass $from, string $collected): HierarchicClassMeta
	{
		return new HierarchicClassMeta(
			$this->createAboveReflectorSource(new ClassSource($from)),
			$this->createParentMeta($from, $collected),
			$this->createInterfacesMeta($from, $collected),
			$this->createTraitsMeta($from, $collected),
			$this->getClassReflectorAttributes($from, $collected),
			$this->createClassConstantsMeta($from, $collected),
			$this->createPropertiesMeta($from, $collected),
			$this->createMethodsMeta($from, $collected),
		);
	}

	/**
	 * @template S of ReflectorSource
	 * @param S $target
	 * @return AboveReflectorSource<S>
	 */
	abstract protected function createAboveReflectorSource(ReflectorSource $target): AboveReflectorSource;

	/**
	 * @template T of object
	 * @param ReflectionClass<object> $class
	 * @param class-string<T>         $attributeClass
	 * @return list<T>
	 */
	abstract protected function getClassReflectorAttributes(ReflectionClass $class, string $attributeClass): array;

	/**
	 * @template T of object
	 * @param ReflectionClass<object> $class
	 * @param class-string<T>         $attributeClass
	 * @return HierarchicClassMeta<T>|null
	 */
	private function createParentMeta(ReflectionClass $class, string $attributeClass): ?HierarchicClassMeta
	{
		$parentClass = $class->getParentClass();

		if ($parentClass === false) {
			return null;
		}

		return $this->collect($parentClass, $attributeClass);
	}

	/**
	 * @template T of object
	 * @param ReflectionClass<object> $class
	 * @param class-string<T>         $attributeClass
	 * @return list<HierarchicClassMeta<T>>
	 */
	private function createInterfacesMeta(ReflectionClass $class, string $attributeClass): array
	{
		$interfaces = [];
		foreach ($class->getInterfaces() as $interface) {
			$interfaces[] = $this->collect($interface, $attributeClass);
		}

		return $interfaces;
	}

	/**
	 * @template T of object
	 * @param ReflectionClass<object> $class
	 * @param class-string<T>         $attributeClass
	 * @return list<HierarchicClassMeta<T>>
	 */
	private function createTraitsMeta(ReflectionClass $class, string $attributeClass): array
	{
		$traits = [];
		foreach ($class->getTraits() as $trait) {
			$traits[] = $this->collect($trait, $attributeClass);
		}

		return $traits;
	}

	/**
	 * @template T of object
	 * @param ReflectionClass<object> $class
	 * @param class-string<T>         $attributeClass
	 * @return list<ClassConstantMeta<T>>
	 */
	private function createClassConstantsMeta(ReflectionClass $class, string $attributeClass): array
	{
		$constants = [];
		foreach ($class->getReflectionConstants() as $constant) {
			$constants[] = new ClassConstantMeta(
				$this->createAboveReflectorSource(new ClassConstantSource($constant)),
				$this->getClassConstantReflectorAttributes($constant, $attributeClass),
			);
		}

		return $constants;
	}

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
	 * @param ReflectionClass<object> $class
	 * @param class-string<T>         $attributeClass
	 * @return list<PropertyMeta<T>>
	 */
	private function createPropertiesMeta(ReflectionClass $class, string $attributeClass): array
	{
		$properties = [];
		foreach ($class->getProperties() as $property) {
			if ($property->getDeclaringClass()->getName() !== $class->getName()) {
				// We don't want parent public and protected properties, they are collected individually
				// Stop acting weird, PHP
				continue;
			}

			$properties[] = new PropertyMeta(
				$this->createAboveReflectorSource(new PropertySource($property)),
				$this->getPropertyReflectorAttributes($property, $attributeClass),
			);
		}

		return $properties;
	}

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
	 * @param ReflectionClass<object> $class
	 * @param class-string<T>         $attributeClass
	 * @return list<MethodMeta<T>>
	 */
	private function createMethodsMeta(ReflectionClass $class, string $attributeClass): array
	{
		$methods = [];
		foreach ($class->getMethods() as $method) {
			$methods[] = new MethodMeta(
				$this->createAboveReflectorSource(new MethodSource($method)),
				$this->getMethodReflectorAttributes($method, $attributeClass),
				$this->createParametersMeta($method, $attributeClass),
			);
		}

		return $methods;
	}

	/**
	 * @template T of object
	 * @param class-string<T> $attributeClass
	 * @return list<T>
	 */
	abstract protected function getMethodReflectorAttributes(ReflectionMethod $method, string $attributeClass): array;

	/**
	 * @template T of object
	 * @param class-string<T> $attributeClass
	 * @return list<ParameterMeta<T>>
	 */
	private function createParametersMeta(ReflectionMethod $method, string $attributeClass): array
	{
		$parameters = [];
		foreach ($method->getParameters() as $parameter) {
			$parameters[] = new ParameterMeta(
				$this->createAboveReflectorSource(new ParameterSource($parameter)),
				$this->getParameterReflectorAttributes($parameter, $attributeClass),
			);
		}

		return $parameters;
	}

	/**
	 * @template T of object
	 * @param class-string<T> $attributeClass
	 * @return list<T>
	 */
	abstract protected function getParameterReflectorAttributes(
		ReflectionParameter $parameter,
		string $attributeClass
	): array;

}
