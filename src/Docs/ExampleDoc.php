<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\ArgsContext;

final class ExampleDoc implements Doc
{

	public const
		Content = 'content',
		Description = 'description';

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public static function resolveArgs(array $args, ArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::Content, self::Description]);

		$checker->checkRequiredArg(self::Content);
		$checker->checkString(self::Content);

		if ($checker->hasArg(self::Description)) {
			$checker->checkNullableString(self::Description);
		} else {
			$args[self::Description] = null;
		}

		return $args;
	}

	public static function getUniqueName(): string
	{
		return 'example';
	}

}
