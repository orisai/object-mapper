<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Attributes\Expect\ListOf;
use Orisai\ObjectMapper\Attributes\Expect\MappedObjectValue;
use Orisai\ObjectMapper\MappedObject;

final class CircularCVO extends MappedObject
{

	/** @ListOf(@MappedObjectValue(CircularAVO::class)) */
	public array $as;

}
