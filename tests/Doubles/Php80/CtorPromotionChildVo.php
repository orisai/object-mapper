<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Php80;

use Orisai\ObjectMapper\Rules\StringValue;

final class CtorPromotionChildVo extends CtorPromotionParentVo
{

	public function __construct(
		string $a,
		public string $b,
		#[StringValue]
		public string $c = 'overriden',
		#[StringValue]
		public string $d = 'baz',
	)
	{
		parent::__construct($a, $b, $c);
	}

}
