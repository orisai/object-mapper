<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Meta;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Type;

/**
 * @implements Rule<EmptyArgs>
 */
final class WrongArgsTypeRule implements Rule
{

	public function resolveArgs(array $args, ArgsContext $context): Args
	{
		return new EmptyArgs();
	}

	public function getArgsType(): string
	{
		/** @phpstan-ignore-next-line */
		return 'nonsense';
	}

	public function processValue($value, Args $args, FieldContext $context)
	{
		return $value;
	}

	public function createType(Args $args, TypeContext $context): Type
	{
		return new SimpleValueType('test');
	}

}
