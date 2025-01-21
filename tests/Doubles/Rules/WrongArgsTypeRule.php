<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Meta\Context\MetaContext;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Rules\NullArgs;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Type;

/**
 * @implements Rule<NullArgs>
 */
final class WrongArgsTypeRule implements Rule
{

	public function resolveArgs(array $args, MetaContext $context): Args
	{
		return new NullArgs(false);
	}

	public function getArgsType(): string
	{
		/** @phpstan-ignore-next-line */
		return EmptyArgs::class;
	}

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
	): Type
	{
		return new SimpleValueType('test');
	}

}
