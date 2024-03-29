<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Modifiers;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Context\ArgsContext;

/**
 * @template-covariant T of Args
 */
interface Modifier
{

	/**
	 * @param array<int|string, mixed> $args
	 * @return T
	 */
	public static function resolveArgs(array $args, ArgsContext $context): Args;

}
