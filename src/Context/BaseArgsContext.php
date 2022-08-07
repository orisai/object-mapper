<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\MetaResolver;
use ReflectionClass;

/**
 * @internal
 */
abstract class BaseArgsContext
{

	/** @var ReflectionClass<MappedObject> */
	private ReflectionClass $class;

	private MetaResolver $metaResolver;

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	public function __construct(ReflectionClass $class, MetaResolver $metaResolver)
	{
		$this->class = $class;
		$this->metaResolver = $metaResolver;
	}

	/**
	 * @return ReflectionClass<MappedObject>
	 */
	public function getClass(): ReflectionClass
	{
		return $this->class;
	}

	public function getMetaResolver(): MetaResolver
	{
		return $this->metaResolver;
	}

}
