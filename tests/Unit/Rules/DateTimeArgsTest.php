<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use DateTime;
use DateTimeImmutable;
use Orisai\ObjectMapper\Rules\DateTimeArgs;
use Orisai\ObjectMapper\Rules\DateTimeRule;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\CustomDateTimeImmutable;
use function serialize;
use function unserialize;

final class DateTimeArgsTest extends TestCase
{

	public function test(): void
	{
		$args = new DateTimeArgs(DateTimeImmutable::class, DateTimeRule::FormatIsoCompat);

		self::assertSame(DateTimeImmutable::class, $args->class);
		self::assertSame(DateTimeRule::FormatIsoCompat, $args->format);
		self::assertTrue($args->isImmutable());

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

	public function testVariant(): void
	{
		$args = new DateTimeArgs(DateTime::class, DateTimeRule::FormatIsoCompat);

		self::assertSame(DateTime::class, $args->class);
		self::assertSame(DateTimeRule::FormatIsoCompat, $args->format);
		self::assertFalse($args->isImmutable());

		self::assertEquals(
			unserialize(serialize($args)),
			$args,
		);
	}

	public function testImmutable(): void
	{
		$args = new DateTimeArgs(CustomDateTimeImmutable::class, DateTimeRule::FormatIsoCompat);

		self::assertTrue($args->isImmutable());
	}

}
