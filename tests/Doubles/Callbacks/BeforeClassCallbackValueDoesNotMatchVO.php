<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\ObjectMapper\Callbacks\BeforeValidation;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\Value;

/**
 * @BeforeValidation(method="before")
 */
final class BeforeClassCallbackValueDoesNotMatchVO implements MappedObject
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
