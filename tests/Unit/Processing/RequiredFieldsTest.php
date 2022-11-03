<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Processing;

use Orisai\ObjectMapper\Processing\RequiredFields;
use PHPUnit\Framework\TestCase;
use ValueError;

final class RequiredFieldsTest extends TestCase
{

	public function test(): void
	{
		self::assertSame(1, RequiredFields::nonDefault()->value);
		self::assertSame('NonDefault', RequiredFields::nonDefault()->name);
		self::assertSame(2, RequiredFields::all()->value);
		self::assertSame('All', RequiredFields::all()->name);
		self::assertSame(3, RequiredFields::none()->value);
		self::assertSame('None', RequiredFields::none()->name);

		self::assertSame(
			[
				RequiredFields::nonDefault(),
				RequiredFields::all(),
				RequiredFields::none(),
			],
			RequiredFields::cases(),
		);

		self::assertSame(RequiredFields::nonDefault(), RequiredFields::from(1));
		self::assertSame(RequiredFields::nonDefault(), RequiredFields::tryFrom(1));

		self::assertNull(RequiredFields::tryFrom(4));
		$this->expectException(ValueError::class);
		RequiredFields::from(4);
	}

}
