<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\Meta\ArgsChecker;

final class ExampleDoc implements Doc
{

	public const CONTENT = 'content';
	public const DESCRIPTION = 'description';

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public static function resolveArgs(array $args, ArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::CONTENT, self::DESCRIPTION]);

		$checker->checkRequiredArg(self::CONTENT);
		$checker->checkString(self::CONTENT);

		if ($checker->hasArg(self::DESCRIPTION)) {
			$checker->checkNullableString(self::DESCRIPTION);
		} else {
			$args[self::DESCRIPTION] = null;
		}

		return $args;
	}

	public static function getUniqueName(): string
	{
		return 'example';
	}

}
