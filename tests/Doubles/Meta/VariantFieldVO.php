<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Meta;

use Orisai\ObjectMapper\Rules\StringValue;

final class VariantFieldVO extends VariantFieldParentVO
{

	/** @StringValue() */
	public string $field;

}
