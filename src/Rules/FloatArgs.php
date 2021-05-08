<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Meta\Args;
use function array_key_exists;

final class FloatArgs implements Args
{

	public ?float $min = null;

	public ?float $max = null;

	public bool $unsigned = true;

	public bool $castNumericString = false;

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

		if (array_key_exists(FloatRule::MIN, $args)) {
			$self->min = $args[FloatRule::MIN];
		}

		if (array_key_exists(FloatRule::MAX, $args)) {
			$self->max = $args[FloatRule::MAX];
		}

		if (array_key_exists(FloatRule::UNSIGNED, $args)) {
			$self->unsigned = $args[FloatRule::UNSIGNED];
		}

		if (array_key_exists(FloatRule::CAST_NUMERIC_STRING, $args)) {
			$self->castNumericString = $args[FloatRule::CAST_NUMERIC_STRING];
		}

		return $self;
	}

}
