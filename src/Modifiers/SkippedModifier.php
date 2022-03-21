<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Modifiers;

use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Context\ArgsContext;

/**
 * @implements Modifier<EmptyArgs>
 */
final class SkippedModifier implements Modifier
{

	/**
	 * {@inheritDoc}
	 */
	public static function resolveArgs(array $args, ArgsContext $context): EmptyArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkNoArgs();

		return new EmptyArgs();
	}

}
