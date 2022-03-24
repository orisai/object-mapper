<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Context\FieldSetContext;
use Orisai\ObjectMapper\Exceptions\InvalidData;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Types\Value;

/**
 * @After(method="after")
 */
final class AfterClassCallbackCurrentTypeInvalidDataVO extends MappedObject
{

	/** @StringValue() */
	public string $string;

	/**
	 * @param array<mixed> $data
	 * @throws InvalidData
	 */
	public static function after(array $data, FieldSetContext $context): void
	{
		$type = $context->getType();
		$type->markInvalid();

		throw InvalidData::create($type, Value::of($data));
	}

}
