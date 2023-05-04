<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance\TraitInsteadOf2;

use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

trait A
{

	/**
	 * Trait is defined in a way that method should always return $string-a but class may override this method
	 *
	 * @StringValue()
	 * @After("collidingMethod")
	 */
	public string $string;

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

final class TraitInsteadOf2VO implements MappedObject
{

	use A;
	use B {
		B::collidingMethod insteadof A;
	}

}
