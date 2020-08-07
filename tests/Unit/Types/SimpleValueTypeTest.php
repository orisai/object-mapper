<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Types\SimpleValueType;
use PHPUnit\Framework\TestCase;

final class SimpleValueTypeTest extends TestCase
{

	public function testType(): void
	{
		$type = new SimpleValueType('string');

		self::assertSame('string', $type->getType());
	}

	public function testParameters(): void
	{
		$type = new SimpleValueType('string', ['a' => 'b', 'c' => true, 'd' => false]);

		self::assertSame(['a' => 'b', 'c' => true, 'd' => false], $type->getParameters());
		self::assertFalse($type->hasInvalidParameters());
		self::assertFalse($type->isParameterInvalid('a'));
		self::assertFalse($type->isParameterInvalid('c'));
		self::assertFalse($type->isParameterInvalid('d'));

		$type->markParameterInvalid('a');
		$type->markParametersInvalid(['c', 'd']);

		self::assertTrue($type->hasInvalidParameters());
		self::assertTrue($type->isParameterInvalid('a'));
		self::assertTrue($type->isParameterInvalid('c'));
		self::assertTrue($type->isParameterInvalid('d'));
	}

	public function testParameterNeverSet(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage('Cannot mark parameter a invalid because it was never set');

		$type = new SimpleValueType('string');
		$type->markParameterInvalid('a');
	}

}
