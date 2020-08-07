<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Meta\Args;
use function array_key_exists;

final class DateTimeArgs implements Args
{

	public ?string $format = null;

	private function __construct()
	{
	}

	/**
	 * @param array<mixed> $args
	 */
	public static function fromArray(array $args): self
	{
		$self = new self();

		if (array_key_exists(DateTimeRule::FORMAT, $args)) {
			$self->format = $args[DateTimeRule::FORMAT];
		}

		return $self;
	}

}
