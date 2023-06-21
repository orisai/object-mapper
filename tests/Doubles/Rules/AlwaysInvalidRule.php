<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Rules\NoArgsRule;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Types\MessageType;

/**
 * @phpstan-implements Rule<EmptyArgs>
 */
final class AlwaysInvalidRule implements Rule
{

	use NoArgsRule;

	/**
	 * @param mixed     $value
	 * @param EmptyArgs $args
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		throw ValueDoesNotMatch::create($this->createType($args, $context), Value::of($value));
	}

	/**
	 * @param EmptyArgs $args
	 */
	public function createType(Args $args, TypeContext $context): MessageType
	{
		return new MessageType('Always invalid');
	}

}
