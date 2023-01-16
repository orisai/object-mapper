<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Circular;

use Orisai\ObjectMapper\Attributes\Expect\ListOf;
use Orisai\ObjectMapper\Attributes\Expect\MappedObjectValue;
use Orisai\ObjectMapper\MappedObject;

final class CircularCVO implements MappedObject
{

	/** @ListOf(@MappedObjectValue(CircularAVO::class)) */
	public array $as;

}
