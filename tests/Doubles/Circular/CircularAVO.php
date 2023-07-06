<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Circular;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\MappedObjectValue;
use Orisai\ObjectMapper\Rules\NullValue;
use Orisai\ObjectMapper\Rules\StringValue;

final class CircularAVO implements MappedObject
{

	/** @MappedObjectValue(CircularBVO::class) */
	public CircularBVO $b;

	/**
	 * @AnyOf({
	 *     @StringValue(),
	 *     @NullValue(),
	 * })
	 */
	public ?string $stringOrNull = null;

	public function __construct(CircularBVO $b, ?string $stringOrNull = null)
	{
		$this->b = $b;
		$this->stringOrNull = $stringOrNull;
	}

}
