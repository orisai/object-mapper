<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Processing\Context;

use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;
use ReflectionProperty;

/**
 * @readonly
 */
final class PropertyContext
{

	private DefaultValueMeta $default;

	private ReflectionProperty $property;

	/** @var int|string */
	private $fieldName;

	/**
	 * @param int|string $fieldName
	 */
	public function __construct(
		DefaultValueMeta $default,
		ReflectionProperty $property,
		$fieldName
	)
	{
		$this->default = $default;
		$this->property = $property;
		$this->fieldName = $fieldName;
	}

	public function hasDefaultValue(): bool
	{
		return $this->default->hasValue();
	}

	/**
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		return $this->default->getValue();
	}

	public function getPropertyName(): string
	{
		return $this->property->getName();
	}

	/**
	 * @return int|string
	 */
	public function getFieldName()
	{
		return $this->fieldName;
	}

}
