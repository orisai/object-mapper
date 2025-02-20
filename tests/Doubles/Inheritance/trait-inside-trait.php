<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance\TraitInsideTrait;

use Orisai\ObjectMapper\Callbacks\AfterValidation;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

trait A1
{

	private function privateA1(string $string): string
	{
		return "$string-private";
	}

	private function protectedA1(string $string): string
	{
		return "$string-protected";
	}

	private function publicA1(string $string): string
	{
		return "$string-public";
	}

}

trait A2
{

	use A1;

}

abstract class P1 implements MappedObject
{

}

final class TraitInsideTraitVO extends P1
{

	use A2;

	/**
	 * @StringValue()
	 * @AfterValidation("privateA1")
	 * @AfterValidation("protectedA1")
	 * @AfterValidation("publicA1")
	 */
	public string $string;

}
