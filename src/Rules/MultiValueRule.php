<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

/**
 * @phpstan-template T of MultiValueArgs
 * @phpstan-implements Rule<T>
 */
abstract class MultiValueRule implements Rule
{

	public const
		ITEM_RULE = 'item',
		MIN_ITEMS = 'minItems',
		MAX_ITEMS = 'maxItems',
		MERGE_DEFAULTS = 'mergeDefaults';

}
