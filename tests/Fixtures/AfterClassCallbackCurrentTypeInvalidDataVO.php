<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\ObjectMapper\Annotation\Callback\After;
use Orisai\ObjectMapper\Annotation\Expect\StringValue;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Context\FieldSetContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\ValueObject;

/**
 * @After(method="after", runtime=CallbackRuntime::ALWAYS)
 */
final class AfterClassCallbackCurrentTypeInvalidDataVO extends ValueObject
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

		throw InvalidData::create($type);
	}

}
