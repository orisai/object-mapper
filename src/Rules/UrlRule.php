<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\Types\SimpleValueType;
use function is_string;
use function parse_url;

/**
 * @phpstan-implements Rule<EmptyArgs>
 */
final class UrlRule implements Rule
{

	use NoArgsRule;

	/**
	 * @param mixed $value
	 * @param EmptyArgs $args
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context): string
	{
		if (is_string($value) && @parse_url($value) !== false) {
			return $value;
		}

		throw ValueDoesNotMatch::create($this->createType($args, $context), $value);
	}

	/**
	 * @param EmptyArgs $args
	 */
	public function createType(Args $args, TypeContext $context): SimpleValueType
	{
		return new SimpleValueType('url');
	}

}
