<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\ObjectMapper\Annotation\Callback\After;
use Orisai\ObjectMapper\Annotation\Expect\StringValue;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\StructureType;
use Orisai\ObjectMapper\ValueObject;

/**
 * @After(method="after", runtime=CallbackRuntime::ALWAYS)
 */
final class AfterClassCallbackNewTypeValidationExceptionVO extends ValueObject
{

	/** @StringValue() */
	public string $string;

	/**
	 * @throws InvalidData
	 */
	public static function after(): void
	{
		$type = new StructureType(EmptyVO::class);
		$type->addError(new MessageType('test'));

		throw InvalidData::create($type);
	}

}
