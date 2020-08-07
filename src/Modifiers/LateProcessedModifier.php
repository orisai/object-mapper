<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Modifiers;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\Meta\ArgsChecker;
use function array_walk_recursive;
use function get_class;
use function gettype;
use function is_object;
use function is_resource;
use function sprintf;

final class LateProcessedModifier implements Modifier
{

	public const CONTEXT = 'context';

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public static function processArgs(array $args, ArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::CONTEXT]);

		if ($checker->hasArg(self::CONTEXT)) {
			$checker->checkArray(self::CONTEXT);

			array_walk_recursive($args[self::CONTEXT], static function (&$item): void {
				if (is_resource($item) || is_object($item)) {
					throw InvalidArgument::create()
						->withMessage(sprintf(
							'Argument "%s" given to "%s" expected to be array of "%s", one of values was "%s".',
							self::CONTEXT,
							self::class,
							'string|int|float|bool|null|array',
							is_object($item) ? get_class($item) : gettype($item),
						));
				}
			});
		} else {
			$args[self::CONTEXT] = [];
		}

		return $args;
	}

}
