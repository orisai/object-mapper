<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use function array_key_exists;

/**
 * @internal
 */
final class BoolArgs implements Args
{

	public bool $castBoolLike = false;

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

		if (array_key_exists(BoolRule::CAST_BOOL_LIKE, $args)) {
			$self->castBoolLike = $args[BoolRule::CAST_BOOL_LIKE];
		}

		return $self;
	}

}
