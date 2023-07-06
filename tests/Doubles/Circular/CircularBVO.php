<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Circular;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\MappedObjectValue;
use Orisai\ObjectMapper\Rules\NullValue;

final class CircularBVO implements MappedObject
{

	/**
	 * @AnyOf({
	 *     @MappedObjectValue(CircularCVO::class),
	 *     @NullValue(),
	 * })
	 */
	public ?CircularCVO $c;

	public function __construct(?CircularCVO $c)
	{
		$this->c = $c;
	}

}
