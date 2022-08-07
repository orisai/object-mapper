<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\MetaResolver;
use ReflectionClass;
use ReflectionProperty;

final class ResolverArgsContext extends BaseArgsContext
{

	private ?ReflectionProperty $property;

	private function __construct(ReflectionClass $class, ?ReflectionProperty $property, MetaResolver $metaResolver)
	{
		parent::__construct($class, $metaResolver);
		$this->property = $property;
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	public static function forClass(ReflectionClass $class, MetaResolver $metaResolver): self
	{
		return new self($class, null, $metaResolver);
	}

	public static function forProperty(ReflectionProperty $property, MetaResolver $metaResolver): self
	{
		return new self($property->getDeclaringClass(), $property, $metaResolver);
	}

	public function getProperty(): ?ReflectionProperty
	{
		return $this->property;
	}

}
