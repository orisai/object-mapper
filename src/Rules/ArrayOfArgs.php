<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;

/**
 * @internal
 */
final class ArrayOfArgs extends MultiValueArgs
{

	public ?RuleRuntimeMeta $keyRuleMeta;

	public function __construct(
		RuleRuntimeMeta $itemRuleMeta,
		?RuleRuntimeMeta $keyRuleMeta = null,
		?int $minItems = null,
		?int $maxItems = null,
		bool $mergeDefaults = false
	)
	{
		parent::__construct($itemRuleMeta, $minItems, $maxItems, $mergeDefaults);
		$this->keyRuleMeta = $keyRuleMeta;
	}

}
