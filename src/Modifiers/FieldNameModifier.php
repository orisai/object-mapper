<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Modifiers;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\ArgsContext;
use function is_int;
use function is_string;

/**
 * @implements Modifier<FieldNameArgs>
 */
final class FieldNameModifier implements Modifier
{

	public const Name = 'name';

	public static function resolveArgs(array $args, ArgsContext $context): FieldNameArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::Name]);
		$checker->checkRequiredArg(self::Name);

		$name = $args[self::Name];
		if (!is_string($name) && !is_int($name)) {
			throw InvalidArgument::create()
				->withMessage($checker->formatMessage(
					'int|string',
					self::Name,
					$name,
				));
		}

		return new FieldNameArgs($name);
	}

}
