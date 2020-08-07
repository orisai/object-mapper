<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

class UninitializedPropertyContext
{

	/** @var int|string */
	private $fieldName;

	/** @var mixed */
	private $value;

	/**
	 * @param int|string $fieldName
	 * @param mixed $value
	 */
	public function __construct($fieldName, $value)
	{
		$this->fieldName = $fieldName;
		$this->value = $value;
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

}
