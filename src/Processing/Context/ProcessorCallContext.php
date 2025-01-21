<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing\Context;

use Closure;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;
use Orisai\ObjectMapper\Processing\ObjectHolder;
use Orisai\ObjectMapper\Types\MappedObjectType;

/**
 * @template T of MappedObject
 */
final class ProcessorCallContext
{

	/** @var ObjectHolder<T> */
	private ObjectHolder $objectHolder;

	private RuntimeMeta $meta;

	/** @var Closure(): MappedObjectType */
	private Closure $typeCreator;

	private ?MappedObjectType $type = null;

	/**
	 * @param ObjectHolder<T> $objectHolder
	 * @param Closure(): MappedObjectType $typeCreator
	 */
	public function __construct(
		ObjectHolder $objectHolder,
		RuntimeMeta $meta,
		Closure $typeCreator
	)
	{
		$this->objectHolder = $objectHolder;
		$this->meta = $meta;
		$this->typeCreator = $typeCreator;
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

	public function getType(): MappedObjectType
	{
		if ($this->type !== null) {
			return $this->type;
		}

		$type = ($this->typeCreator)();
		unset($this->typeCreator);

		return $this->type = $type;
	}

	public function getTypeIfInitialized(): ?MappedObjectType
	{
		return $this->type;
	}

}
