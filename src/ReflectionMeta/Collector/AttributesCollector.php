<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\ReflectionMeta\Collector;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\SourceMap\AboveReflectorSource;
use Orisai\SourceMap\AttributeSource;
use Orisai\SourceMap\EmptyAboveReflectorSource;
use Orisai\SourceMap\ReflectorSource;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use function assert;
use function is_a;
use function method_exists;
use const PHP_VERSION_ID;

final class AttributesCollector extends BaseCollector
{

	public function __construct()
	{
		if (!self::canBeConstructed()) {
			throw InvalidState::create()
				->withMessage('Attributes are supported since PHP 8.0');
		}
	}

	public static function canBeConstructed(): bool
	{
		return PHP_VERSION_ID >= 8_00_00;
	}

	protected function getClassReflectorAttributes(ReflectionClass $class, string $attributeClass): array
	{
		return $this->reflectionAttributesToFilteredInstances(
			$class->getAttributes(),
			$attributeClass,
		);
	}

	protected function getPropertyReflectorAttributes(ReflectionProperty $property, string $attributeClass): array
	{
		return $this->reflectionAttributesToFilteredInstances(
			$property->getAttributes(),
			$attributeClass,
		);
	}

	protected function getMethodReflectorAttributes(ReflectionMethod $method, string $attributeClass): array
	{
		return $this->reflectionAttributesToFilteredInstances(
			$method->getAttributes(),
			$attributeClass,
		);
	}

	protected function getClassConstantReflectorAttributes(
		ReflectionClassConstant $constant,
		string $attributeClass
	): array
	{
		return $this->reflectionAttributesToFilteredInstances(
			$constant->getAttributes(),
			$attributeClass,
		);
	}

	protected function getParameterReflectorAttributes(ReflectionParameter $parameter, string $attributeClass): array
	{
		return $this->reflectionAttributesToFilteredInstances(
			$parameter->getAttributes(),
			$attributeClass,
		);
	}

	protected function createAboveReflectorSource(ReflectorSource $target): AboveReflectorSource
	{
		$reflector = $target->getReflector();
		assert(method_exists($reflector, 'getAttributes'));

		if ($reflector->getAttributes() === []) {
			return new EmptyAboveReflectorSource($target);
		}

		return new AttributeSource($target);
	}

	/**
	 * @template T of object
	 * @param array<ReflectionAttribute<object>> $reflectionAttributes
	 * @param class-string<T>                    $attributeClass
	 * @return list<T>
	 */
	private function reflectionAttributesToFilteredInstances(array $reflectionAttributes, string $attributeClass): array
	{
		$attributes = [];
		foreach ($reflectionAttributes as $attribute) {
			if (!is_a($attribute->getName(), $attributeClass, true)) {
				continue;
			}

			$instance = $attribute->newInstance();
			assert($instance instanceof $attributeClass);
			$attributes[] = $instance;
		}

		return $attributes;
	}

}
