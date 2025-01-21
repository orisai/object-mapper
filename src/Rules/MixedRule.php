<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Types\SimpleValueType;

/**
 * @implements Rule<EmptyArgs>
 */
final class MixedRule implements Rule
{

	use NoArgsRule;

	/**
	 * @param mixed $value
	 * @param EmptyArgs $args
	 * @return mixed
	 */
	public function processValue(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	)
	{
		return $value;
	}

	public function createType(
		Args $args,
		ServicesContext $services,
		DynamicContext $dynamic
	): SimpleValueType
	{
		return new SimpleValueType('mixed');
	}

}
