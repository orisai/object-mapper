<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Php80;

use Orisai\ObjectMapper\Rules\StringValue;

final class CtorPromotionChildVo extends CtorPromotionParentVo
{

	public function __construct(
		string $a,
		string $b,
		public string $c = 'overridden',
		#[StringValue]
		public string $d = 'baz',
	)
	{
		parent::__construct($a, $b, $c);
	}

}
