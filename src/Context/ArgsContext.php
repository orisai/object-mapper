<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\Meta\MetaResolver;
use Orisai\ObjectMapper\ValueObject;
use ReflectionClass;
use ReflectionProperty;

class ArgsContext
{

	/** @var ReflectionClass<ValueObject> */
	private ReflectionClass $class;
	private ?ReflectionProperty $property;
	private MetaResolver $metaResolver;

	/**
	 * @param ReflectionClass<ValueObject> $class
	 */
	public function __construct(ReflectionClass $class, ?ReflectionProperty $property, MetaResolver $metaResolver)
	{
		$this->class = $class;
		$this->property = $property;
		$this->metaResolver = $metaResolver;
	}

	/**
	 * @return ReflectionClass<ValueObject>
	 */
	public function getClass(): ReflectionClass
	{
		return $this->class;
	}

	public function getProperty(): ?ReflectionProperty
	{
		return $this->property;
	}

	public function getMetaResolver(): MetaResolver
	{
		return $this->metaResolver;
	}

}
