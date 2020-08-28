<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\ObjectMapper\Annotation\Expect\StringValue;
use Orisai\ObjectMapper\Annotation\Modifiers\FieldName;
use Orisai\ObjectMapper\ValueObject;

final class FieldNameIdenticalWithAnotherPropertyNameVO extends ValueObject
{

	/** @StringValue() */
	public string $field;

	/**
	 * @StringValue()
	 * @FieldName("field")
	 */
	public string $property;

}
