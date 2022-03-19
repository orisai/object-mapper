<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\Meta\DocMeta;

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
		/** @var array<DocMeta> $links */
		$links = $checker->checkArray(self::LINKS);

		$resolver = $context->getMetaResolver();
		$optimized = [];

		foreach ($links as $link) {
			$optimized[$link->getName()::getUniqueName()]
				= $resolver->resolveDocMeta($link, $context);
		}

		return $optimized;
	}

	public static function getUniqueName(): string
	{
		return 'links';
	}

}
