<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\ObjectMapper\Callbacks\Before;
use Orisai\ObjectMapper\Context\MappedObjectContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Rules\StringValue;
use Orisai\ObjectMapper\Types\MessageType;

/**
 * @Before("before")
 */
final class InvalidateFieldBeforeClassVO implements MappedObject
{

	/** @StringValue() */
	public string $string;

	/** @StringValue() */
	public string $alsoString;

	/**
	 * @param mixed $values
	 */
	public static function before($values, MappedObjectContext $context): void
	{
		$context->getType()->overwriteInvalidField(
			'string',
			ValueDoesNotMatch::create(
				new MessageType('invalidated in before callback'),
				Value::none(),
			),
		);
	}

}
