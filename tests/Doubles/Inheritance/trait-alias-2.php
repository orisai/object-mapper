<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance\TraitAlias2;

use Orisai\ObjectMapper\Callbacks\AfterValidation;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

trait A
{

	/**
	 * @StringValue()
	 * @AfterValidation("originalMethod")
	 */
	public string $string;

	private function originalMethod(string $string): string
	{
		return "$string-a";
	}

}

final class TraitAlias2VO implements MappedObject
{

	use A {
		A::originalMethod as renamedMethod;
	}

}
