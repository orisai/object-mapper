<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Circular;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\ListOf;
use Orisai\ObjectMapper\Rules\MappedObjectValue;

final class CircularCVO implements MappedObject
{

	/**
	 * @var list<CircularAVO>
	 *
	 * @ListOf(@MappedObjectValue(CircularAVO::class))
	 */
	public array $as;

	/**
	 * @param list<CircularAVO> $as
	 */
	public function __construct(array $as)
	{
		$this->as = $as;
	}

}
