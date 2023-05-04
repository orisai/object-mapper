<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance\TraitAlias3;

use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

trait A
{

	private function originalMethod(string $string): string
	{
		return "$string-a";
	}

}

final class TraitAlias3VO implements MappedObject
{

	use A {
		A::originalMethod as renamedMethod;
	}

	/**
	 * Method's original name is available via reflection even after being aliased
	 *
	 * @StringValue()
	 * @After("originalMethod")
	 */
	public string $string;

}
