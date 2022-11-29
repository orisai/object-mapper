<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use DateTime;
use DateTimeImmutable;
use Orisai\ObjectMapper\Args\Args;
use function is_a;

/**
 * @internal
 */
final class DateTimeArgs implements Args
{

	/** @var class-string<DateTimeImmutable|DateTime> */
	public string $type;

	public string $format;

	/**
	 * @param class-string<DateTimeImmutable|DateTime> $type
	 */
	public function __construct(string $type = DateTimeImmutable::class, string $format = DateTimeRule::FormatIsoCompat)
	{
		$this->type = $type;
		$this->format = $format;
	}

	public function isImmutable(): bool
	{
		return is_a($this->type, DateTimeImmutable::class, true);
	}

}
