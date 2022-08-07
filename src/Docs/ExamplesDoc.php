<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\ResolverArgsContext;
use Orisai\ObjectMapper\Meta\DocMeta;

final class ExamplesDoc implements Doc
{

	private const Examples = 'examples';

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public static function resolveArgs(array $args, ResolverArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::Examples]);

		$checker->checkRequiredArg(self::Examples);
		/** @var array<DocMeta> $examples */
		$examples = $checker->checkArray(self::Examples);

		$resolver = $context->getMetaResolver();
		$optimized = [];

		foreach ($examples as $example) {
			$optimized[$example->getName()::getUniqueName()]
				= $resolver->resolveDocMeta($example, $context);
		}

		return $optimized;
	}

	public static function getUniqueName(): string
	{
		return 'examples';
	}

}
