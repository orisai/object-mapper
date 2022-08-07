<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\ResolverArgsContext;

final class LinkDoc implements Doc
{

	private const
		Url = 'url',
		Description = 'description';

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public static function resolveArgs(array $args, ResolverArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::Url, self::Description]);

		$checker->checkRequiredArg(self::Url);
		$checker->checkString(self::Url);

		if ($checker->hasArg(self::Description)) {
			$checker->checkNullableString(self::Description);
		} else {
			$args[self::Description] = null;
		}

		return $args;
	}

	public static function getUniqueName(): string
	{
		return 'link';
	}

}
