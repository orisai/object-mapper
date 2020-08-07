<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Meta\Args;
use function array_key_exists;

final class IntArgs implements Args
{

	public ?int $min = null;
	public ?int $max = null;
	public bool $unsigned = true;
	public bool $castIntLike = false;

	private function __construct()
	{
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

		if (array_key_exists(IntRule::CAST_INT_LIKE, $args)) {
			$self->castIntLike = $args[IntRule::CAST_INT_LIKE];
		}

		return $self;
	}

}
