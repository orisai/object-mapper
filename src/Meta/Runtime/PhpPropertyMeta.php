<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\MappedObject;
use ReflectionClass;
use ReflectionProperty;
use const PHP_VERSION_ID;

/**
 * @readonly
 */
final class PhpPropertyMeta
{

	/** @var class-string<MappedObject> */
	public string $declaringClass;

	public string $name;

	public bool $isPublicSet;

	/**
	 * @param class-string<MappedObject> $declaringClass
	 */
	public function __construct(
		string $declaringClass,
		string $name,
		bool $isPublicSet
	)
	{
		$this->declaringClass = $declaringClass;
		$this->name = $name;
		$this->isPublicSet = $isPublicSet;
	}

	public static function from(ReflectionProperty $property): self
	{
		/** @var ReflectionClass<MappedObject> $class */
		$class = $property->getDeclaringClass();

		return new self(
			$class->getName(),
			$property->getName(),
			self::isPublicSet($property),
		);
	}

	private static function isPublicSet(ReflectionProperty $property): bool
	{
		return $property->isPublic()
			&& (PHP_VERSION_ID < 8_01_00 || !$property->isReadOnly());
	}

}
