<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Modifiers;

use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\ArgsContext;

/**
 * @implements Modifier<DefaultValueArgs>
 */
final class DefaultValueModifier implements Modifier
{

	/** @internal */
	public const Value = 'value';

	public static function resolveArgs(array $args, ArgsContext $context): DefaultValueArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::Value]);
		$checker->checkRequiredArg(self::Value);

		return new DefaultValueArgs($args[self::Value]);
	}

}
