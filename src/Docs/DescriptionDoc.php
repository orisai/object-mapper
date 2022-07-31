<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\ArgsContext;

final class DescriptionDoc implements Doc
{

	private const Message = 'message';

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public static function resolveArgs(array $args, ArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::Message]);

		$checker->checkRequiredArg(self::Message);
		$checker->checkString(self::Message);

		return $args;
	}

	public static function getUniqueName(): string
	{
		return 'description';
	}

}
