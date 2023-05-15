<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;

/**
 * @internal
 */
final class ArrayOfArgs extends MultiValueArgs
{

	/** @var RuleRuntimeMeta<Args>|null */
	public ?RuleRuntimeMeta $keyRuleMeta;

	/**
	 * @param RuleRuntimeMeta<Args>      $itemRuleMeta
	 * @param RuleRuntimeMeta<Args>|null $keyRuleMeta
	 */
	public function __construct(
		RuleRuntimeMeta $itemRuleMeta,
		?RuleRuntimeMeta $keyRuleMeta,
		?int $minItems,
		?int $maxItems,
		bool $mergeDefaults
	)
	{
		parent::__construct($itemRuleMeta, $minItems, $maxItems, $mergeDefaults);
		$this->keyRuleMeta = $keyRuleMeta;
	}

}
