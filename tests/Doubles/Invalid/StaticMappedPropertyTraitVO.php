<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Invalid;

use Orisai\ObjectMapper\Rules\StringValue;

trait StaticMappedPropertyTraitVO
{

	/** @StringValue() */
	public static string $field;

}
