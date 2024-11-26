<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Invalid;

use Orisai\ObjectMapper\Rules\StringValue;

class VariantFieldVO extends VariantFieldParentVO
{

	/** @StringValue() */
	public string $field;

}
