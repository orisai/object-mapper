<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\Types\SimpleValueType;

/**
 * @phpstan-implements Rule<EmptyArgs>
 */
final class MixedRule implements Rule
{

	use NoArgsRule;

	/**
	 * @param mixed $value
	 * @param EmptyArgs $args
	 * @return mixed
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		return $value;
	}

	/**
	 * @param EmptyArgs $args
	 */
	public function createType(Args $args, TypeContext $context): SimpleValueType
	{
		return new SimpleValueType('mixed');
	}

}
