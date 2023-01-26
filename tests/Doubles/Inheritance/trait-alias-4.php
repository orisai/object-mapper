<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance\TraitAlias4;

use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

trait A
{

	/**
	 * Method's new name is available via reflection in scope of trait after being aliased in class
	 *
	 * @StringValue()
	 * @After("renamedMethod")
	 */
	public string $string;

	private function originalMethod(string $string): string
	{
		return "$string-a";
	}

}

final class TraitAlias4VO implements MappedObject
{

	use A {
		A::originalMethod as renamedMethod;
	}

}
