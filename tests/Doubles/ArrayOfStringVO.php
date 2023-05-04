<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\ArrayOf;
use Orisai\ObjectMapper\Rules\StringValue;

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
