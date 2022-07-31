<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

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

}
