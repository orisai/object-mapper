<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance;

use Orisai\ObjectMapper\Callbacks\AfterValidation;

final class FieldCallbackChildVo extends FieldCallbackParentVo
{

	/**
	 * @AfterValidation("testChildAfterValidation")
	 * @AfterValidation("testChildAfterValidation")
	 */
	public string $test;

	public function testChildAfterValidation(string $value): string
	{
		return $value . '-child';
	}

}
