<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Annotation\Expect\ArrayOf;
use Orisai\ObjectMapper\Annotation\Expect\IntValue;
use Orisai\ObjectMapper\MappedObject;

final class ArrayOfIntVO extends MappedObject
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
