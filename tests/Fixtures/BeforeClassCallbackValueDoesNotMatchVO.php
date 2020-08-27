<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\ObjectMapper\Annotation\Callback\Before;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\ValueObject;

/**
 * @Before(method="before", runtime=CallbackRuntime::ALWAYS)
 */
final class BeforeClassCallbackValueDoesNotMatchVO extends ValueObject
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
