<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Orisai\ObjectMapper\Args\Args;
use function is_a;

/**
 * @internal
 */
final class DateTimeArgs implements Args
{

	public string $format;

	/** @var class-string<DateTimeImmutable|DateTime> */
	public string $type;

	/**
	 * @param class-string<DateTimeImmutable|DateTime> $type
	 */
	public function __construct(string $format = DateTimeInterface::ATOM, string $type = DateTimeImmutable::class)
	{
		$this->format = $format;
		$this->type = $type;
	}

	public function isImmutable(): bool
	{
		return is_a($this->type, DateTimeImmutable::class, true);
	}

}
