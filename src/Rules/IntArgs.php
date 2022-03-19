<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use function array_key_exists;

final class IntArgs implements Args
{

	public ?int $min = null;

	public ?int $max = null;

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

		if (array_key_exists(IntRule::MIN, $args)) {
			$self->min = $args[IntRule::MIN];
		}

		if (array_key_exists(IntRule::MAX, $args)) {
			$self->max = $args[IntRule::MAX];
		}

		if (array_key_exists(IntRule::UNSIGNED, $args)) {
			$self->unsigned = $args[IntRule::UNSIGNED];
		}

		if (array_key_exists(IntRule::CAST_NUMERIC_STRING, $args)) {
			$self->castNumericString = $args[IntRule::CAST_NUMERIC_STRING];
		}

		return $self;
	}

}
