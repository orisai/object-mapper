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
	public string $class;

	public string $format;

	/**
	 * @param class-string<DateTimeImmutable|DateTime> $class
	 */
	public function __construct(string $class, string $format)
	{
		$this->class = $class;
		$this->format = $format;
	}

	public function isImmutable(): bool
	{
		return is_a($this->class, DateTimeImmutable::class, true);
	}

}
