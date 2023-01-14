<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

final class SkippedFieldContext
{

	private string $propertyName;

	/** @var mixed */
	private $value;

	private bool $isDefault;

	/**
	 * @param mixed $value
	 */
	public function __construct(string $propertyName, $value, bool $isDefault)
	{
		$this->propertyName = $propertyName;
		$this->value = $value;
		$this->isDefault = $isDefault;
	}

	public function getPropertyName(): string
	{
		return $this->propertyName;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	public function isDefault(): bool
	{
		return $this->isDefault;
	}

}
