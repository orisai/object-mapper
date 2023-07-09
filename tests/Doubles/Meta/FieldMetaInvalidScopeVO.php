<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Meta;

use Orisai\ObjectMapper\Rules\StringValue;

abstract class FieldMetaInvalidScopeVO
{

	/** @StringValue() */
	public string $field;

}
