<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\Exceptions\Logic\NotImplemented;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Callbacks\Callback;
use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\Context\BaseFieldContext;
use Orisai\ObjectMapper\Processing\ObjectHolder;
use Orisai\ObjectMapper\Rules\NullArgs;
use ReflectionClass;
use Reflector;

/**
 * @implements Callback<NullArgs>
 */
final class WrongArgsTypeCallback implements Callback
{

	public static function resolveArgs(array $args, ArgsContext $context, Reflector $reflector): Args
	{
		return new NullArgs(false);
	}

	public static function getArgsType(): string
	{
		/** @phpstan-ignore-next-line */
		return EmptyArgs::class;
	}

	public static function invoke(
		$data,
		Args $args,
		ObjectHolder $holder,
		BaseFieldContext $context,
		ReflectionClass $declaringClass
	): void
	{
		throw NotImplemented::create();
	}

}
