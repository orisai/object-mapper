<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Meta;

use Orisai\ObjectMapper\Rules\StringValue;

trait StaticMappedPropertyTraitVO
{

	/** @StringValue() */
	public static string $field;

}
