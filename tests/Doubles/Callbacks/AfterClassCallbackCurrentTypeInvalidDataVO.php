<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\ObjectMapper\Callbacks\After;
use Orisai\ObjectMapper\Context\MappedObjectContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Rules\StringValue;

/**
 * @After(method="after")
 */
final class AfterClassCallbackCurrentTypeInvalidDataVO implements MappedObject
{

	/** @StringValue() */
	public string $string;

	/**
	 * @param array<mixed> $data
	 * @throws InvalidData
	 */
	public static function after(array $data, MappedObjectContext $context): void
	{
		$type = $context->getType();
		$type->markInvalid();

		throw InvalidData::create($type, Value::of($data));
	}

}
