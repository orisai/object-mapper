<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\MetaResolver;
use ReflectionClass;
use ReflectionProperty;

class ArgsContext
{

	/** @var ReflectionClass<MappedObject> */
	private ReflectionClass $class;

	private ?ReflectionProperty $property;

	private MetaResolver $metaResolver;

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	public function __construct(ReflectionClass $class, ?ReflectionProperty $property, MetaResolver $metaResolver)
	{
		$this->class = $class;
		$this->property = $property;
		$this->metaResolver = $metaResolver;
	}

	/**
	 * @return ReflectionClass<MappedObject>
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
