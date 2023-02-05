<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Types\GenericArrayType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\Value;
use PHPUnit\Framework\TestCase;

final class GenericArrayTypeTest extends TestCase
{

	public function testName(): void
	{
		$type = GenericArrayType::forArray(null, new MessageType('test'));
		self::assertSame('array', $type->getName());

		$type = GenericArrayType::forList(null, new MessageType('test'));
		self::assertSame('list', $type->getName());
	}

	public function testPairs(): void
	{
		$keyType = new MessageType('test');
		$itemType = new MessageType('test');
		$type = GenericArrayType::forArray($keyType, $itemType);

		self::assertSame($itemType, $type->getItemType());
		self::assertSame($keyType, $type->getKeyType());
	}

	public function testInvalidPairs(): void
	{
		$itemType = new MessageType('test');
		$type = GenericArrayType::forArray(null, $itemType);

		self::assertSame($itemType, $type->getItemType());
		self::assertNull($type->getKeyType());

		self::assertFalse($type->hasInvalidPairs());
		self::assertSame([], $type->getInvalidPairs());

		$key1 = 123;
		$invalidKey1 = ValueDoesNotMatch::create(new MessageType('test'), Value::none());
		$invalidItem1 = ValueDoesNotMatch::create(new MessageType('test'), Value::none());
		$type->addInvalidPair($key1, $invalidKey1, $invalidItem1);

		$key2 = 'foo';
		$invalidKey2 = null;
		$invalidItem2 = ValueDoesNotMatch::create(new MessageType('test'), Value::none());
		$type->addInvalidPair($key2, $invalidKey2, $invalidItem2);

		$key3 = 'bar';
		$invalidKey3 = ValueDoesNotMatch::create(new MessageType('test'), Value::none());
		$invalidItem3 = null;
		$type->addInvalidPair($key3, $invalidKey3, $invalidItem3);

		self::assertTrue($type->hasInvalidPairs());
		$pairs = $type->getInvalidPairs();
		self::assertCount(3, $pairs);

		$pair1 = $pairs[$key1];
		self::assertSame($invalidKey1, $pair1->getKey());
		self::assertSame($invalidItem1, $pair1->getValue());

		$pair2 = $pairs[$key2];
		self::assertNull($pair2->getKey());
		self::assertSame($invalidItem2, $pair2->getValue());

		$pair3 = $pairs[$key3];
		self::assertSame($invalidKey3, $pair3->getKey());
		self::assertNull($pair3->getValue());
	}

	public function testInvalidKeyThenValue(): void
	{
		$itemType = new MessageType('test');
		$type = GenericArrayType::forArray(null, $itemType);

		$invalidKey = ValueDoesNotMatch::create(new MessageType('test'), Value::none());
		$type->addInvalidKey(1, $invalidKey);

		$pair = $type->getInvalidPairs()[1];
		self::assertSame($invalidKey, $pair->getKey());
		self::assertNull($pair->getValue());

		$invalidValue = ValueDoesNotMatch::create(new MessageType('test'), Value::none());
		$type->addInvalidValue(1, $invalidValue);
		$pair = $type->getInvalidPairs()[1];

		self::assertSame($invalidKey, $pair->getKey());
		self::assertSame($invalidValue, $pair->getValue());
	}

	public function testInvalidValueThenKey(): void
	{
		$itemType = new MessageType('test');
		$type = GenericArrayType::forArray(null, $itemType);

		$invalidValue = ValueDoesNotMatch::create(new MessageType('test'), Value::none());
		$type->addInvalidValue(1, $invalidValue);

		$pair = $type->getInvalidPairs()[1];
		self::assertNull($pair->getKey());
		self::assertSame($invalidValue, $pair->getValue());

		$invalidKey = ValueDoesNotMatch::create(new MessageType('test'), Value::none());
		$type->addInvalidKey(1, $invalidKey);

		$pair = $type->getInvalidPairs()[1];
		self::assertSame($invalidKey, $pair->getKey());
		self::assertSame($invalidValue, $pair->getValue());
	}

	public function testInvalidPairFailure(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage('At least one of key type and item type of invalid pair should not be null');

		$type = GenericArrayType::forArray(null, new MessageType('test'));
		$type->addInvalidPair(123, null, null);
	}

}
