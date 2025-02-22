<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\MappedObject;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use function in_array;

/**
 * @readonly
 */
final class PhpMethodMeta
{

	/** @var class-string<MappedObject> */
	public string $declaringClass;

	public string $method;

	public bool $isPublic;

	public bool $isStatic;

	public bool $returnsValue;

	/**
	 * @param class-string<MappedObject> $declaringClass
	 */
	public function __construct(
		string $declaringClass,
		string $method,
		bool $isPublic,
		bool $isStatic,
		bool $returnsValue
	)
	{
		$this->declaringClass = $declaringClass;
		$this->method = $method;
		$this->isPublic = $isPublic;
		$this->isStatic = $isStatic;
		$this->returnsValue = $returnsValue;
	}

	public static function from(ReflectionMethod $method): self
	{
		/** @var ReflectionClass<MappedObject> $class */
		$class = $method->getDeclaringClass();

		return new self(
			$class->getName(),
			$method->getName(),
			$method->isPublic(),
			$method->isStatic(),
			self::getMethodReturnsValue($method),
		);
	}

	/**
	 * Method is expected to return data unless void or never return type is defined
	 */
	private static function getMethodReturnsValue(ReflectionMethod $method): bool
	{
		return !in_array(self::getTypeName($method->getReturnType()), ['void', 'never'], true);
	}

	private static function getTypeName(?ReflectionType $type): ?string
	{
		if (!$type instanceof ReflectionNamedType) {
			return null;
		}

		return $type->getName();
	}

}
