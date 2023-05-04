<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;
use Orisai\ObjectMapper\Types\Value;

/**
 * @After(method="after")
 */
final class AfterClassCallbackValueDoesNotMatchVO implements MappedObject
{

	/** @StringValue() */
	public string $string;

	/**
	 * @param array<mixed> $data
	 * @throws ValueDoesNotMatch
	 */
	public static function after(array $data): void
	{
		throw ValueDoesNotMatch::createFromString('Error after class', Value::none());
	}

}
