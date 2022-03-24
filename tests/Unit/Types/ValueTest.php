<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Types\Value;
use PHPUnit\Framework\TestCase;

final class ValueTest extends TestCase
{

	public function testValue(): void
	{
		$value = Value::of('value');
		self::assertTrue($value->has());
		self::assertSame('value', $value->get());
	}

	public function testNone(): void
	{
		$value = Value::none();
		self::assertFalse($value->has());

		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage('Check if value exists with Orisai\ObjectMapper\Types\Value::has()');
		$value->get();
	}

}
