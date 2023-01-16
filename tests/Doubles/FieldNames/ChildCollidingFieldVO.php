<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\FieldNames;

use Orisai\ObjectMapper\Attributes\Expect\StringValue;

final class ChildCollidingFieldVO extends ParentFieldVO
{

	/** @StringValue() */
	private string $property;

}
