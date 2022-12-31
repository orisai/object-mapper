<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Attributes\Expect\ArrayOf;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class ArrayOfStringVO implements MappedObject
{

	/**
	 * @var array<string>
	 *
	 * @ArrayOf(
	 *     @StringValue()
	 * )
	 */
	public array $items;

}
