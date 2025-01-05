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

	/** @var array<int|string, SkippedFieldContext> */
	private array $skippedFields = [];

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

	/**
	 * @param int|string $fieldName
	 */
	public function addSkippedField(
		$fieldName,
		SkippedFieldContext $skippedFieldContext
	): void
	{
		$this->skippedFields[$fieldName] = $skippedFieldContext;
	}

	/**
	 * @return array<int|string, SkippedFieldContext>
	 */
	public function getSkippedFields(): array
	{
		return $this->skippedFields;
	}

}
