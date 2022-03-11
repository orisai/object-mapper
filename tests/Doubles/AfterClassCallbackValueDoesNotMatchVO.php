<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Annotation\Callback\After;
use Orisai\ObjectMapper\Annotation\Expect\StringValue;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;

/**
 * @After(method="after", runtime=CallbackRuntime::ALWAYS)
 */
final class AfterClassCallbackValueDoesNotMatchVO extends MappedObject
{

	/** @StringValue() */
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
