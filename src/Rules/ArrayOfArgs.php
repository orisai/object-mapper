<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Meta\RuleMeta;
use function array_key_exists;

final class ArrayOfArgs extends MultiValueArgs
{

	public ?RuleMeta $keyMeta = null;

	/**
	 * @param array<mixed> $args
	 * @return static
	 */
	public static function fromArray(array $args): self
	{
		$self = parent::fromArray($args);

		if (array_key_exists(ArrayOfRule::KEY_RULE, $args) && $args[ArrayOfRule::KEY_RULE] !== null) {
			$self->keyMeta = RuleMeta::fromArray($args[ArrayOfRule::KEY_RULE]);
		}

		return $self;
	}

}
