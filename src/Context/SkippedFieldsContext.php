<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Types\MappedObjectType;

final class SkippedFieldsContext
{

	private MappedObjectType $type;

	private Options $options;

	/** @var array<int|string, SkippedFieldContext> */
	private array $skippedFields = [];

	public function __construct(MappedObjectType $type, Options $options)
	{
		$this->type = $type;
		$this->options = $options;
	}

	public function getType(): MappedObjectType
	{
		return $this->type;
	}

	public function getOptions(): Options
	{
		return $this->options;
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
