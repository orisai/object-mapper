<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance;

use Orisai\ObjectMapper\Callbacks\AfterValidation;
use Orisai\ObjectMapper\MappedObject;

abstract class FieldCallbackParentVo implements MappedObject
{

	use FieldCallbackParentTraitVo;

	/** @AfterValidation("testParentAfterValidation") */
	public string $test;

	public function __construct(string $test)
	{
		$this->test = $test;
	}

	public function testParentAfterValidation(string $value): string
	{
		return $value . '-parent';
	}

}
