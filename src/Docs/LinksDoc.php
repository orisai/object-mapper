<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\ArgsContext;

final class LinksDoc implements Doc
{

	public const LINKS = 'links';

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public static function resolveArgs(array $args, ArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::LINKS]);

		$checker->checkRequiredArg(self::LINKS);
		$links = $checker->checkArray(self::LINKS);

		$resolver = $context->getMetaResolver();
		$optimized = [];

		foreach ($links as $key => $link) {
			[, , $args] = $resolver->resolveDocMeta($link, $context);
			$optimized[$key] = $args;
		}

		return $optimized;
	}

	public static function getUniqueName(): string
	{
		return 'links';
	}

}
