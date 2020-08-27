<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Modifiers;

use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\Meta\ArgsChecker;

final class SkippedModifier implements Modifier
{

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public static function processArgs(array $args, ArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkNoArgs();

		return [];
	}

}
