<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Attributes\Expect\MappedObjectValue;
use Orisai\ObjectMapper\MappedObject;

final class CircularAVO extends MappedObject
{

	/** @MappedObjectValue(CircularBVO::class) */
	public CircularBVO $b;

}
