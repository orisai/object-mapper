<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use function array_key_exists;
use function sprintf;

/**
 * @internal
 */
final class ValueEnumArgs implements Args
{

	public bool $useKeys = false;

	/** @var array<mixed> */
	public array $values;

	private function __construct()
	{
		// Static constructor is required
	}

	/**
	 * @param array<mixed> $args
	 */
	public static function fromArray(array $args): self
	{
		$self = new self();

		if (array_key_exists(ValueEnumRule::VALUES, $args)) {
			$self->values = $args[ValueEnumRule::VALUES];
		} else {
			throw InvalidArgument::create()
				->withMessage(sprintf('Key "%s" is required', ValueEnumRule::VALUES));
		}

		if (array_key_exists(ValueEnumRule::USE_KEYS, $args)) {
			$self->useKeys = $args[ValueEnumRule::USE_KEYS];
		}

		return $self;
	}

}
