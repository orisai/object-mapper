<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\FieldNames;

use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

abstract class ParentFieldVO implements MappedObject
{

	/** @StringValue() */
	private string $property;

	public function getParentProperty(): string
	{
		return $this->property;
	}

}
