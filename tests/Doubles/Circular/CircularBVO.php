<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Circular;

use Orisai\ObjectMapper\Attributes\Expect\AnyOf;
use Orisai\ObjectMapper\Attributes\Expect\MappedObjectValue;
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\MappedObject;

final class CircularBVO implements MappedObject
{

	/**
	 * @AnyOf({
	 *     @MappedObjectValue(CircularCVO::class),
	 *     @NullValue(),
	 * })
	 */
	public ?CircularCVO $c;

}
