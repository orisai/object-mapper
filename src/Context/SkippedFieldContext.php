<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use ReflectionProperty;

final class SkippedFieldContext
{

	private ReflectionProperty $property;

	/** @var mixed */
	private $value;

	private bool $isDefault;

	/**
	 * @param mixed $value
	 */
	public function __construct(ReflectionProperty $property, $value, bool $isDefault)
	{
		$this->property = $property;
		$this->value = $value;
		$this->isDefault = $isDefault;
	}

	public function getProperty(): ReflectionProperty
	{
		return $this->property;
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
