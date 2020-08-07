<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\ObjectMapper\Annotation\Callback;
use Orisai\ObjectMapper\Annotation\Expect;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\ValueObject;

/**
 * @Callback\After(method="after", runtime=CallbackRuntime::ALWAYS)
 */
final class AfterClassCallbackRuleExceptionVO extends ValueObject
{

	/** @Expect\StringValue() */
	public string $string;

	/**
	 * @param array<mixed> $data
	 * @throws ValueDoesNotMatch
	 */
	public static function after(array $data): void
	{
		throw ValueDoesNotMatch::createFromString('Error after class');
	}

}
