<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Meta\ArgsCreator;

abstract class MultiValueRule implements Rule
{

	use ArgsCreator;

	public const
		ITEM_RULE = 'item',
		MIN_ITEMS = 'minItems',
		MAX_ITEMS = 'maxItems',
		MERGE_DEFAULTS = 'mergeDefaults';

}
