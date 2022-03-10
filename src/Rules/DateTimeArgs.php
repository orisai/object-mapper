<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Orisai\ObjectMapper\Meta\Args;
use function array_key_exists;

final class DateTimeArgs implements Args
{

	public string $format = DateTimeInterface::ATOM;

	/** @var class-string<DateTimeImmutable|DateTime> */
	public string $type = DateTimeImmutable::class;

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

		if (array_key_exists(DateTimeRule::FORMAT, $args)) {
			$self->format = $args[DateTimeRule::FORMAT];
		}

		if (array_key_exists(DateTimeRule::TYPE, $args)) {
			$self->type = $args[DateTimeRule::TYPE];
		}

		return $self;
	}

}
