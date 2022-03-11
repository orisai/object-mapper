<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Annotation\Expect\StringValue;
use Orisai\ObjectMapper\Annotation\Modifiers\FieldName;
use Orisai\ObjectMapper\MappedObject;

final class FieldNameIdenticalWithAnotherPropertyNameVO extends MappedObject
{

	/** @StringValue() */
	public string $field;

	/**
	 * @StringValue()
	 * @FieldName("field")
	 */
	public string $property;

}
