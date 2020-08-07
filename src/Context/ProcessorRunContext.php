<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\Meta\Meta;
use Orisai\ObjectMapper\Processing\ObjectHolder;
use Orisai\ObjectMapper\ValueObject;

class ProcessorRunContext
{

	private ObjectHolder $objectHolder;
	private Meta $meta;

	/** @phpstan-var class-string<ValueObject> */
	private string $class;

	/** @var array<string, UninitializedPropertyContext> */
	private array $skippedProperties = [];

	/**
	 * @phpstan-param class-string<ValueObject> $class
	 */
	public function __construct(string $class, ObjectHolder $objectHolder, Meta $meta)
	{
		$this->class = $class;
		$this->objectHolder = $objectHolder;
		$this->meta = $meta;
	}

	/**
	 * @phpstan-return class-string<ValueObject>
	 */
	public function getClass(): string
	{
		return $this->class;
	}

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
		UninitializedPropertyContext $uninitializedPropertyContext
	): void
	{
		$this->skippedProperties[$propertyName] = $uninitializedPropertyContext;
	}

	/**
	 * @return array<string, UninitializedPropertyContext>
	 */
	public function getSkippedProperties(): array
	{
		return $this->skippedProperties;
	}

}
