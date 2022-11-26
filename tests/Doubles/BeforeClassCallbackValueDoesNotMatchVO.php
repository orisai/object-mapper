<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Attributes\Callbacks\Before;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Types\Value;

/**
 * @Before(method="before")
 */
final class BeforeClassCallbackValueDoesNotMatchVO extends MappedObject
{

	/**
	 * @param mixed $data
	 * @throws ValueDoesNotMatch
	 */
	protected static function before($data): void
	{
		throw ValueDoesNotMatch::createFromString('Error before class', Value::none());
	}

}
