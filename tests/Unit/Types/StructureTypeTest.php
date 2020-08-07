<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\StructureType;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Fixtures\DefaultsVO;

final class StructureTypeTest extends TestCase
{

	public function testClass(): void
	{
		$type = new StructureType(DefaultsVO::class);

		self::assertSame(DefaultsVO::class, $type->getClass());
	}

	public function testInvalid(): void
	{
		$type = new StructureType(DefaultsVO::class);

		self::assertFalse($type->isInvalid());
		$type->markInvalid();
		self::assertTrue($type->isInvalid());
	}

	public function testErrors(): void
	{
		$type = new StructureType(DefaultsVO::class);

		self::assertSame([], $type->getErrors());
		self::assertFalse($type->hasErrors());

		$error1 = new MessageType('t');
		$type->addError($error1);

		$error2 = new MessageType('t');
		$type->addError($error2);

		self::assertTrue($type->hasErrors());
		self::assertSame([$error1, $error2], $type->getErrors());
	}

	public function testFields(): void
	{
		$type = new StructureType(DefaultsVO::class);

		self::assertSame([], $type->getFields());

		$key1 = 'foo';
		$field1 = new MessageType('t');
		$type->addField($key1, $field1);

		$key2 = 'bar';
		$field2 = new MessageType('t');
		$type->addField($key2, $field2);

		$key3 = 123;
		$field3 = new MessageType('t');
		$type->addField($key3, $field3);

		self::assertSame(
			[
				$key1 => $field1,
				$key2 => $field2,
				$key3 => $field3,
			],
			$type->getFields(),
		);

		self::assertFalse($type->hasInvalidFields());
		self::assertFalse($type->isFieldInvalid($key1));
		self::assertFalse($type->isFieldInvalid($key2));
		self::assertFalse($type->isFieldInvalid($key3));

		$invalid1 = new MessageType('t');
		$type->overwriteInvalidField($key1, $invalid1);

		self::assertTrue($type->hasInvalidFields());
		self::assertTrue($type->isFieldInvalid($key1));
		self::assertFalse($type->isFieldInvalid($key2));
		self::assertFalse($type->isFieldInvalid($key3));
		self::assertSame(
			[
				$key1 => $invalid1,
				$key2 => $field2,
				$key3 => $field3,
			],
			$type->getFields(),
		);
	}

}
