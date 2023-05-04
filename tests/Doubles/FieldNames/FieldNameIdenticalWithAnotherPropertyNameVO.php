<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\FieldNames;

use Orisai\ObjectMapper\Attributes\Modifiers\FieldName;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

final class FieldNameIdenticalWithAnotherPropertyNameVO implements MappedObject
{

	/** @StringValue() */
	public string $field;

	/**
	 * @StringValue()
	 * @FieldName("field")
	 */
	public string $property;

}
