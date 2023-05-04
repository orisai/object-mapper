<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance\TraitAlias1;

use Orisai\ObjectMapper\Callbacks\After;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

trait A
{

	private function originalMethod(string $string): string
	{
		return "$string-a";
	}

}

final class TraitAlias1VO implements MappedObject
{

	use A {
		A::originalMethod as renamedMethod;
	}

	/**
	 * @StringValue()
	 * @After("renamedMethod")
	 */
	public string $string;

}
