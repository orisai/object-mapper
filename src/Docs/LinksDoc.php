<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\ResolverArgsContext;
use Orisai\ObjectMapper\Meta\Shared\DocMeta;

final class LinksDoc implements Doc
{

	private const Links = 'links';

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public static function resolveArgs(array $args, ResolverArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::Links]);

		$checker->checkRequiredArg(self::Links);
		/** @var array<DocMeta> $links */
		$links = $checker->checkArray(self::Links);

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
