<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Meta\Args;
use function array_key_exists;

final class StringArgs implements Args
{

	public ?string $pattern = null;

	public bool $notEmpty = false;

	public ?int $minLength = null;

	public ?int $maxLength = null;

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

		if (array_key_exists(StringRule::PATTERN, $args)) {
			$self->pattern = $args[StringRule::PATTERN];
		}

		if (array_key_exists(StringRule::NOT_EMPTY, $args)) {
			$self->notEmpty = $args[StringRule::NOT_EMPTY];
		}

		if (array_key_exists(StringRule::MIN_LENGTH, $args)) {
			$self->minLength = $args[StringRule::MIN_LENGTH];
		}

		if (array_key_exists(StringRule::MAX_LENGTH, $args)) {
			$self->maxLength = $args[StringRule::MAX_LENGTH];
		}

		return $self;
	}

}
