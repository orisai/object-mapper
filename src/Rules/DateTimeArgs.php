<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use DateTime;
use DateTimeImmutable;
use Orisai\ObjectMapper\Meta\Args;
use function array_key_exists;

final class DateTimeArgs implements Args
{

	public ?string $format = null;

	/** @phpstan-var class-string<DateTimeImmutable|DateTime> */
	public string $type = DateTimeImmutable::class;

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

		if (array_key_exists(DateTimeRule::TYPE, $args)) {
			$self->type = $args[DateTimeRule::TYPE];
		}

		return $self;
	}

}
