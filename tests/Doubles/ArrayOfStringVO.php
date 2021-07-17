<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Annotation\Expect\ArrayOf;
use Orisai\ObjectMapper\Annotation\Expect\StringValue;
use Orisai\ObjectMapper\ValueObject;

final class ArrayOfStringVO extends ValueObject
{
	/**
	 * @var array<string>
	 * @ArrayOf(
	 *     @StringValue()
	 * )
	 */
	public array $items;
}
