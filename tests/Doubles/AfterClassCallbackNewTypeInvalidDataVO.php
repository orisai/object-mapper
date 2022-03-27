<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\StructureType;
use Orisai\ObjectMapper\Types\Value;

/**
 * @After(method="after")
 */
final class AfterClassCallbackNewTypeInvalidDataVO extends MappedObject
{

	/** @StringValue() */
	public string $string;

	/**
	 * @throws InvalidData
	 */
	public static function after(): void
	{
		$type = new StructureType(EmptyVO::class);
		$type->addError(
			ValueDoesNotMatch::create(
				new MessageType('test'),
				Value::none(),
			),
		);

		throw InvalidData::create($type, Value::none());
	}

}
