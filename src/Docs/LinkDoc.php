<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\ArgsContext;

final class LinkDoc implements Doc
{

	public const
		URL = 'url',
		DESCRIPTION = 'description';

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public static function resolveArgs(array $args, ArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::URL, self::DESCRIPTION]);

		$checker->checkRequiredArg(self::URL);
		$checker->checkString(self::URL);

		if ($checker->hasArg(self::DESCRIPTION)) {
			$checker->checkNullableString(self::DESCRIPTION);
		} else {
			$args[self::DESCRIPTION] = null;
		}

		return $args;
	}

	public static function getUniqueName(): string
	{
		return 'link';
	}

}
