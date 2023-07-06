<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\FieldNames;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

abstract class ParentFieldVO implements MappedObject
{

	/** @StringValue() */
	private string $property;

	public function __construct(string $property)
	{
		$this->property = $property;
	}

	public function getParentProperty(): string
	{
		return $this->property;
	}

}
