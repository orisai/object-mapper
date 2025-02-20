<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\ObjectMapper\Callbacks\AfterValidation;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Rules\StringValue;

/**
 * @AfterValidation(method="after")
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
