<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Context\TypeContext;

/**
 * @phpstan-template T of MultiValueArgs
 * @phpstan-implements Rule<T>
 */
abstract class MultiValueRule implements Rule
{

	/** @internal */
	public const
		ItemRule = 'item',
		MinItems = 'minItems',
		MaxItems = 'maxItems',
		MergeDefaults = 'mergeDefaults';

	/**
	 * @return array{Rule<Args>, Args}
	 */
	protected function getItemRuleArgs(MultiValueArgs $args, TypeContext $context): array
	{
		$itemRuleMeta = $args->itemRuleMeta;

		$itemRule = $context->getRule($itemRuleMeta->getType());
		$itemArgs = $itemRuleMeta->getArgs();

		return [$itemRule, $itemArgs];
	}

}
