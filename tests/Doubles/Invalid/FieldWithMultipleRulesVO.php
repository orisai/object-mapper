<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Invalid;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\IntValue;
use Orisai\ObjectMapper\Rules\StringValue;

class FieldWithMultipleRulesVO implements MappedObject
{

	/**
	 * @StringValue()
	 * @IntValue()
	 */
	public string $field;

}
