<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;
use Orisai\ObjectMapper\Processing\ObjectHolder;

/**
 * @template T of MappedObject
 */
final class ProcessorCallContext
{

	/** @var ObjectHolder<T> */
	private ObjectHolder $objectHolder;

	private RuntimeMeta $meta;

	/**
	 * @param ObjectHolder<T> $objectHolder
	 */
	public function __construct(ObjectHolder $objectHolder, RuntimeMeta $meta)
	{
		$this->objectHolder = $objectHolder;
		$this->meta = $meta;
	}

	/**
	 * @return ObjectHolder<T>
	 */
	public function getObjectHolder(): ObjectHolder
	{
		return $this->objectHolder;
	}

	public function getMeta(): RuntimeMeta
	{
		return $this->meta;
	}

}
