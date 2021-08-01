<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\NoValue;
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
		$type->addInvalidItem(123, ValueDoesNotMatch::create($invalid1, NoValue::create()));
		$type->addInvalidItem('foo', ValueDoesNotMatch::create($invalid2, NoValue::create()));

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
