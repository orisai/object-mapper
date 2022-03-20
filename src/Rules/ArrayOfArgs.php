<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use function array_key_exists;

/**
 * @internal
 */
final class ArrayOfArgs extends MultiValueArgs
{

	public ?RuleRuntimeMeta $keyMeta = null;

	/**
	 * @param array<mixed> $args
	 * @return static
	 */
	public static function fromArray(array $args): self
	{
		$self = parent::fromArray($args);

		if (array_key_exists(ArrayOfRule::KEY_RULE, $args) && $args[ArrayOfRule::KEY_RULE] !== null) {
			$self->keyMeta = $args[ArrayOfRule::KEY_RULE];
		}

		return $self;
	}

}
