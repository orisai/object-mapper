<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;

/**
 * @internal
 */
class MultiValueArgs implements Args
{

	/** @var RuleRuntimeMeta<Args> */
	public RuleRuntimeMeta $itemRuleMeta;

	public ?int $minItems;

	public ?int $maxItems;

	public bool $mergeDefaults;

	/**
	 * @param RuleRuntimeMeta<Args> $itemRuleMeta
	 */
	public function __construct(
		RuleRuntimeMeta $itemRuleMeta,
		?int $minItems,
		?int $maxItems,
		bool $mergeDefaults
	)
	{
		$this->itemRuleMeta = $itemRuleMeta;
		$this->minItems = $minItems;
		$this->maxItems = $maxItems;
		$this->mergeDefaults = $mergeDefaults;
	}

}
