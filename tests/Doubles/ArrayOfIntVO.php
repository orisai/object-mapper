<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\ArrayOf;
use Orisai\ObjectMapper\Rules\IntValue;

final class ArrayOfIntVO implements MappedObject
{

	/**
	 * @var array<int>
	 *
	 * @ArrayOf(
	 *     @IntValue()
	 * )
	 */
	public array $items;

}
