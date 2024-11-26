<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Invalid;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

abstract class VariantFieldParentVO implements MappedObject
{

	/** @StringValue(notEmpty=true) */
	public string $field;

}
