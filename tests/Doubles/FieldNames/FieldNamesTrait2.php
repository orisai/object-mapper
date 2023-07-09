<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\FieldNames;

use Orisai\ObjectMapper\Modifiers\FieldName;
use Orisai\ObjectMapper\Rules\StringValue;

trait FieldNamesTrait2
{

	/**
	 * @StringValue()
	 * @FieldName("field")
	 */
	public string $property2;

}
