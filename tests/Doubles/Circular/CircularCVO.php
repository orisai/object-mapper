<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Circular;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\ListOf;
use Orisai\ObjectMapper\Rules\MappedObjectValue;

final class CircularCVO implements MappedObject
{

	/** @ListOf(@MappedObjectValue(CircularAVO::class)) */
	public array $as;

}
