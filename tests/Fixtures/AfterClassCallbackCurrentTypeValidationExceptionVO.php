<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\ObjectMapper\Annotation\Callback;
use Orisai\ObjectMapper\Annotation\Expect;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Context\FieldSetContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\ValueObject;

/**
 * @Callback\After(method="after", runtime=CallbackRuntime::ALWAYS)
 */
final class AfterClassCallbackCurrentTypeValidationExceptionVO extends ValueObject
{

	/** @Expect\StringValue() */
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
