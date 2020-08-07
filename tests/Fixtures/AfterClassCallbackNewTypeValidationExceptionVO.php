<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\ObjectMapper\Annotation\Callback;
use Orisai\ObjectMapper\Annotation\Expect;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\StructureType;
use Orisai\ObjectMapper\ValueObject;

/**
 * @Callback\After(method="after", runtime=CallbackRuntime::ALWAYS)
 */
final class AfterClassCallbackNewTypeValidationExceptionVO extends ValueObject
{

	/** @Expect\StringValue() */
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
