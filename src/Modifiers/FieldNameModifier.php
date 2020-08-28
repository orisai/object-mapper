<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Modifiers;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\Meta\ArgsChecker;
use function is_int;
use function is_string;

final class FieldNameModifier implements Modifier
{

	public const NAME = 'name';

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public static function processArgs(array $args, ArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::NAME]);
		$checker->checkRequiredArg(self::NAME);

		$name = $args[self::NAME];
		if (!is_string($name) && !is_int($name)) {
			throw InvalidArgument::create()
				->withMessage($checker->formatMessage(
					'int|string',
					self::NAME,
					$name,
				));
		}

		return $args;
	}

}
