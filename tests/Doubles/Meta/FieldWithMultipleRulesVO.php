<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Meta;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\IntValue;
use Orisai\ObjectMapper\Rules\StringValue;

final class FieldWithMultipleRulesVO implements MappedObject
{

	/**
	 * @StringValue()
	 * @IntValue()
	 */
	public string $field;

}
