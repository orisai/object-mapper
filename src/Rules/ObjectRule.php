<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\Types\SimpleValueType;
use function is_object;

/**
 * @phpstan-implements Rule<EmptyArgs>
 */
final class ObjectRule implements Rule
{

	use NoArgsRule;

	/**
	 * @param mixed $value
	 * @param EmptyArgs $args
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context): object
	{
		if (!is_object($value)) {
			throw ValueDoesNotMatch::create($this->createType($args, $context), $value);
		}

		return $value;
	}

	/**
	 * @param EmptyArgs $args
	 */
	public function createType(Args $args, TypeContext $context): SimpleValueType
	{
		return new SimpleValueType('object');
	}

}
