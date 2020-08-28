<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\Meta\ArgsChecker;

final class DescriptionDoc implements Doc
{

	public const MESSAGE = 'message';

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public static function resolveArgs(array $args, ArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::MESSAGE]);

		$checker->checkRequiredArg(self::MESSAGE);
		$checker->checkString(self::MESSAGE);

		return $args;
	}

	public static function getUniqueName(): string
	{
		return 'description';
	}

}
