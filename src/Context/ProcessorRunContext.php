<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\Meta\Meta;
use Orisai\ObjectMapper\Processing\ObjectHolder;
use Orisai\ObjectMapper\ValueObject;

/**
 * @template T of ValueObject
 */
class ProcessorRunContext
{

	/** @var ObjectHolder<T> */
	private ObjectHolder $objectHolder;

	private Meta $meta;

	/** @var class-string<T> */
	private string $class;

	/** @var array<string, SkippedPropertyContext> */
	private array $skippedProperties = [];

	/**
	 * @param class-string<T> $class
	 * @param ObjectHolder<T> $objectHolder
	 */
	public function __construct(string $class, ObjectHolder $objectHolder, Meta $meta)
	{
		$this->class = $class;
		$this->objectHolder = $objectHolder;
		$this->meta = $meta;
	}

	/**
	 * @return class-string<T>
	 */
	public function getClass(): string
	{
		return $this->class;
	}

	/**
	 * @return ObjectHolder<T>
	 */
	public function getObjectHolder(): ObjectHolder
	{
		return $this->objectHolder;
	}

	public function getMeta(): Meta
	{
		return $this->meta;
	}

	public function addSkippedProperty(
		string $propertyName,
		SkippedPropertyContext $uninitializedPropertyContext
	): void
	{
		$this->skippedProperties[$propertyName] = $uninitializedPropertyContext;
	}

	/**
	 * @return array<string, SkippedPropertyContext>
	 */
	public function getSkippedProperties(): array
	{
		return $this->skippedProperties;
	}

}
