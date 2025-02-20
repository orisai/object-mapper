<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance\TraitAlias1;

use Orisai\ObjectMapper\Callbacks\AfterValidation;
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
	 * @AfterValidation("renamedMethod")
	 */
	public string $string;

}
