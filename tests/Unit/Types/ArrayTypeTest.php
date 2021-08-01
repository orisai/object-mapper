<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\NoValue;
use Orisai\ObjectMapper\Types\ArrayType;
use Orisai\ObjectMapper\Types\MessageType;
use PHPUnit\Framework\TestCase;

final class ArrayTypeTest extends TestCase
{

	public function testPairs(): void
	{
		$keyType = new MessageType('test');
		$itemType = new MessageType('test');
		$type = new ArrayType($keyType, $itemType);

		self::assertSame($itemType, $type->getItemType());
		self::assertSame($keyType, $type->getKeyType());
	}

	public function testInvalidPairs(): void
	{
		$itemType = new MessageType('test');
		$type = new ArrayType(null, $itemType);

		self::assertSame($itemType, $type->getItemType());
		self::assertNull($type->getKeyType());

		self::assertFalse($type->hasInvalidPairs());
		self::assertSame([], $type->getInvalidPairs());

		$key1 = 123;
		$invalidKey1 = new MessageType('test');
		$invalidItem1 = new MessageType('test');
		$type->addInvalidPair(
			$key1,
			ValueDoesNotMatch::create($invalidKey1, NoValue::create()),
			ValueDoesNotMatch::create($invalidItem1, NoValue::create()),
		);

		$key2 = 'foo';
		$invalidKey2 = null;
		$invalidItem2 = new MessageType('test');
		$type->addInvalidPair($key2, $invalidKey2, ValueDoesNotMatch::create($invalidItem2, NoValue::create()));

		$key3 = 'bar';
		$invalidKey3 = new MessageType('test');
		$invalidItem3 = null;
		$type->addInvalidPair($key3, ValueDoesNotMatch::create($invalidKey3, NoValue::create()), $invalidItem3);

		self::assertTrue($type->hasInvalidPairs());
		self::assertSame(
			[
				$key1 => [$invalidKey1, $invalidItem1],
				$key2 => [$invalidKey2, $invalidItem2],
				$key3 => [$invalidKey3, $invalidItem3],
			],
			$type->getInvalidPairs(),
		);
	}

	public function testInvalidPairFailure(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage('At least one of key type and item type of invalid pair should not be null');

		$type = new ArrayType(null, new MessageType('test'));
		$type->addInvalidPair(123, null, null);
	}

}
