<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

final class SkippedFieldsContext
{

	private MappedObjectContext $mappedObjectContext;

	/** @var array<int|string, SkippedFieldContext> */
	private array $skippedFields = [];

	public function __construct(MappedObjectContext $mappedObjectContext)
	{
		$this->mappedObjectContext = $mappedObjectContext;
	}

	public function getMappedObjectContext(): MappedObjectContext
	{
		return $this->mappedObjectContext;
	}

	/**
	 * @param int|string $fieldName
	 */
	public function addSkippedField($fieldName, SkippedFieldContext $context): void
	{
		$this->skippedFields[$fieldName] = $context;
	}

	/**
	 * @param int|string $fieldName
	 */
	public function removeSkippedField($fieldName): void
	{
		unset($this->skippedFields[$fieldName]);
	}

	/**
	 * @return array<int|string, SkippedFieldContext>
	 */
	public function getSkippedFields(): array
	{
		return $this->skippedFields;
	}

}
