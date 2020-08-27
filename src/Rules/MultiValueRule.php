<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Meta\ArgsCreator;

abstract class MultiValueRule implements Rule
{

	use ArgsCreator;

	public const ITEM_RULE = 'itemRule';
	public const MIN_ITEMS = 'minItems';
	public const MAX_ITEMS = 'maxItems';
	public const MERGE_DEFAULTS = 'mergeDefaults';

}
