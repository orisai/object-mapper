<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Annotation\Callback\Before;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;

/**
 * @Before(method="before", runtime=CallbackRuntime::ALWAYS)
 */
final class BeforeClassCallbackValueDoesNotMatchVO extends MappedObject
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
