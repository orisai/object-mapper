<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

final class SkippedPropertyContext
{

	/** @var int|string */
	private $fieldName;

	/** @var mixed */
	private $value;

	private bool $isDefault;

	/**
	 * @param int|string $fieldName
	 * @param mixed $value
	 */
	public function __construct($fieldName, $value, bool $isDefault)
	{
		$this->fieldName = $fieldName;
		$this->value = $value;
		$this->isDefault = $isDefault;
	}

	/**
	 * @return int|string
	 */
	public function getFieldName()
	{
		return $this->fieldName;
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
