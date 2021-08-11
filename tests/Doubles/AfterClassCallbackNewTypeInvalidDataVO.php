<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Annotation\Callback\After;
use Orisai\ObjectMapper\Annotation\Expect\StringValue;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\NoValue;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\StructureType;
use Orisai\ObjectMapper\ValueObject;

/**
 * @After(method="after", runtime=CallbackRuntime::ALWAYS)
 */
final class AfterClassCallbackNewTypeInvalidDataVO extends ValueObject
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
				NoValue::create(),
			),
		);

		throw InvalidData::create($type, NoValue::create());
	}

}
