<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\ObjectMapper\Annotation\Callback;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\ValueObject;

/**
 * @Callback\Before(method="before", runtime=CallbackRuntime::ALWAYS)
 */
final class BeforeClassCallbackRuleExceptionVO extends ValueObject
{

	/**
	 * @param array<mixed> $data
	 * @throws ValueDoesNotMatch
	 */
	public static function before(array $data): void
	{
		throw ValueDoesNotMatch::createFromString('Error before class');
	}

}
