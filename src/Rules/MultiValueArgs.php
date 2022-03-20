<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use function array_key_exists;
use function sprintf;

/**
 * @internal
 */
class MultiValueArgs implements Args
{

	public RuleRuntimeMeta $itemMeta;

	public ?int $minItems = null;

	public ?int $maxItems = null;

	public bool $mergeDefaults = false;

	final protected function __construct()
	{
		// Static constructor is required
	}

	/**
	 * @param array<mixed> $args
	 * @return static
	 */
	public static function fromArray(array $args): self
	{
		$self = new static();

		if (array_key_exists(MultiValueRule::ITEM_RULE, $args)) {
			$self->itemMeta = $args[MultiValueRule::ITEM_RULE];
		} else {
			throw InvalidArgument::create()
				->withMessage(sprintf('Key "%s" is required', MultiValueRule::ITEM_RULE));
		}

		if (array_key_exists(MultiValueRule::MIN_ITEMS, $args)) {
			$self->minItems = $args[MultiValueRule::MIN_ITEMS];
		}

		if (array_key_exists(MultiValueRule::MAX_ITEMS, $args)) {
			$self->maxItems = $args[MultiValueRule::MAX_ITEMS];
		}

		if (array_key_exists(MultiValueRule::MERGE_DEFAULTS, $args)) {
			$self->mergeDefaults = $args[MultiValueRule::MERGE_DEFAULTS];
		}

		return $self;
	}

}
