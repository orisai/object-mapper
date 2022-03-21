<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Types\StructureType;

class SkippedPropertiesContext
{

	private StructureType $type;

	private Options $options;

	/** @var array<string, SkippedPropertyContext> */
	private array $skippedProperties = [];

	public function __construct(StructureType $type, Options $options)
	{
		$this->type = $type;
		$this->options = $options;
	}

	public function getType(): StructureType
	{
		return $this->type;
	}

	public function getOptions(): Options
	{
		return $this->options;
	}

	public function addSkippedProperty(string $propertyName, SkippedPropertyContext $context): void
	{
		$this->skippedProperties[$propertyName] = $context;
	}

	public function removeSkippedProperty(string $propertyName): void
	{
		unset($this->skippedProperties[$propertyName]);
	}

	/**
	 * @return array<string, SkippedPropertyContext>
	 */
	public function getSkippedProperties(): array
	{
		return $this->skippedProperties;
	}

}
