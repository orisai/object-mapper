<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Orisai\ObjectMapper\Exceptions\ValueDoesNotMatch;
use Orisai\ObjectMapper\Types\ListType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Value;
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

		$invalid1 = ValueDoesNotMatch::create(new MessageType('test'), Value::none());
		$invalid2 = ValueDoesNotMatch::create(new SimpleValueType('test'), Value::none());
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

}
