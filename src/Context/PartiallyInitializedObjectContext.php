<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\Options;
use Orisai\ObjectMapper\Types\StructureType;

class PartiallyInitializedObjectContext
{

	private StructureType $type;
	private Options $options;

	/** @var array<string, UninitializedPropertyContext> */
	private array $uninitializedProperties = [];

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

	public function addUninitializedProperty(string $propertyName, UninitializedPropertyContext $context): void
	{
		$this->uninitializedProperties[$propertyName] = $context;
	}

	public function removeInitializedProperty(string $propertyName): void
	{
		unset($this->uninitializedProperties[$propertyName]);
	}

	/**
	 * @return array<UninitializedPropertyContext>
	 */
	public function getUninitializedProperties(): array
	{
		return $this->uninitializedProperties;
	}

}
