<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Types\ListType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use PHPUnit\Framework\TestCase;

final class ListTypeTest extends TestCase
{

	public function testItems(): void
	{
		$itemType = new MessageType('test');
		$type = new ListType($itemType);

		self::assertSame($itemType, $type->getItemType());
	}

	public function testInvalidItems(): void
	{
		$itemType = new MessageType('test');
		$type = new ListType($itemType);

		self::assertFalse($type->areKeysInvalid());
		$type->markKeysInvalid();
		self::assertTrue($type->areKeysInvalid());

		self::assertFalse($type->hasInvalidItems());
		self::assertSame([], $type->getInvalidItems());

		$invalid1 = new MessageType('test');
		$invalid2 = new SimpleValueType('test');
		$type->addInvalidItem(123, $invalid1);
		$type->addInvalidItem('foo', $invalid2);

		self::assertTrue($type->hasInvalidItems());
		self::assertSame(
			[
				123 => $invalid1,
				'foo' => $invalid2,
			],
			$type->getInvalidItems(),
		);
	}

	public function testParameters(): void
	{
		$itemType = new MessageType('test');
		$type = new ListType($itemType, ['a' => 'b', 'c' => true, 'd' => false]);

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

		$type = new ListType(new MessageType('test'));
		$type->markParameterInvalid('a');
	}

}
