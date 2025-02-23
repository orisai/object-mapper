<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Php80;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

abstract class CtorPromotionParentVo implements MappedObject
{

	public function __construct(
		#[StringValue]
		public string $a,
		#[StringValue]
		public string $b = 'foo',
		#[StringValue]
		public string $c = 'bar'
	)
	{
	}

}
