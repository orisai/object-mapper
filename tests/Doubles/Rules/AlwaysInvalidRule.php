<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
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
	public function processValue(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	)
	{
		throw ValueDoesNotMatch::create($this->createType($args, $services, $dynamic), Value::of($value));
	}

	/**
	 * @param EmptyArgs $args
	 */
	public function createType(
		Args $args,
		ServicesContext $services,
		DynamicContext $dynamic
	): MessageType
	{
		return new MessageType('Always invalid');
	}

}
