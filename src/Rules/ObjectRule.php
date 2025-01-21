<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Types\SimpleValueType;
use function is_object;

/**
 * @implements Rule<EmptyArgs>
 */
final class ObjectRule implements Rule
{

	use NoArgsRule;

	/**
	 * @param mixed $value
	 * @param EmptyArgs $args
	 * @throws ValueDoesNotMatch
	 */
	public function processValue(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	): object
	{
		if (!is_object($value)) {
			throw ValueDoesNotMatch::create($this->createType($args, $services, $dynamic), Value::of($value));
		}

		return $value;
	}

	public function createType(
		Args $args,
		ServicesContext $services,
		DynamicContext $dynamic
	): SimpleValueType
	{
		return new SimpleValueType('object');
	}

}
