<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\FieldNames;

use Orisai\ObjectMapper\Rules\StringValue;

final class ChildCollidingFieldVO extends ParentFieldVO
{

	/** @StringValue() */
	private string $property;

	public function __construct(string $parentProperty, string $property)
	{
		parent::__construct($parentProperty);
		$this->property = $property;
	}

	public function getProperty(): string
	{
		return $this->property;
	}

}
