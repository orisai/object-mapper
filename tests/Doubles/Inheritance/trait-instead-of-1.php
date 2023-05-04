<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance\TraitInsteadOf1;

use Orisai\ObjectMapper\Callbacks\After;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

trait A
{

	private function collidingMethod(string $string): string
	{
		return "$string-a";
	}

}

trait B
{

	private function collidingMethod(string $string): string
	{
		return "$string-b";
	}

}

final class TraitInstead1OfVO implements MappedObject
{

	use A;
	use B {
		B::collidingMethod insteadof A;
	}

	/**
	 * @StringValue()
	 * @After("collidingMethod")
	 */
	public string $string;

}
