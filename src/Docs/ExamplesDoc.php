<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\Meta\ArgsChecker;

final class ExamplesDoc implements Doc
{

	public const EXAMPLES = 'examples';

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public static function resolveArgs(array $args, ArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::EXAMPLES]);

		$checker->checkRequiredArg(self::EXAMPLES);
		$examples = $checker->checkArray(self::EXAMPLES);

		$resolver = $context->getMetaResolver();
		$optimized = [];

		foreach ($examples as $key => $example) {
			[, , $args] = $resolver->resolveDocMeta($example, $context);
			$optimized[$key] = $args;
		}

		return $optimized;
	}

	public static function getUniqueName(): string
	{
		return 'examples';
	}

}
