<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\ObjectMapper\Callbacks\AfterValidation;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Rules\StringValue;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\MessageType;
use Tests\Orisai\ObjectMapper\Doubles\EmptyVO;

/**
 * @AfterValidation(method="after")
 */
final class AfterClassCallbackNewTypeInvalidDataVO implements MappedObject
{

	/** @StringValue() */
	public string $string;

	/**
	 * @throws InvalidData
	 */
	public static function after(): void
	{
		$type = new MappedObjectType(EmptyVO::class);
		$type->addError(
			ValueDoesNotMatch::create(
				new MessageType('test'),
				Value::none(),
			),
		);

		throw InvalidData::create($type, Value::none());
	}

}
