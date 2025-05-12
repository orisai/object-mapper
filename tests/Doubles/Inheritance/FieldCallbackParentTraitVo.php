<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance;

use Orisai\ObjectMapper\Callbacks\AfterValidation;
use Orisai\ObjectMapper\Rules\StringValue;

trait FieldCallbackParentTraitVo
{

	/**
	 * @StringValue()
	 * @AfterValidation("testParentTraitAfterValidation")
	 */
	public string $test;

	public function testParentTraitAfterValidation(string $value): string
	{
		return $value . '-parentTrait';
	}

}
