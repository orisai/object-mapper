<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use function array_key_exists;

final class NullArgs implements Args
{

	public bool $castEmptyString = false;

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

		if (array_key_exists(NullRule::CAST_EMPTY_STRING, $args)) {
			$self->castEmptyString = $args[NullRule::CAST_EMPTY_STRING];
		}

		return $self;
	}

}
