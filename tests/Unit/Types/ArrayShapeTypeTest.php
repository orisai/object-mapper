<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Types\ArrayShapeType;
use Orisai\ObjectMapper\Types\MessageType;
use PHPUnit\Framework\TestCase;

final class ArrayShapeTypeTest extends TestCase
{

	public function testInvalid(): void
	{
		$type = new ArrayShapeType();

		self::assertFalse($type->isInvalid());
		$type->markInvalid();
		self::assertTrue($type->isInvalid());
	}

	public function testErrors(): void
	{
		$type = new ArrayShapeType();

		self::assertSame([], $type->getErrors());
		self::assertFalse($type->hasErrors());

		$error1 = ValueDoesNotMatch::create(
			new MessageType('t'),
			Value::none(),
		);
		$type->addError($error1);

		$error2 = ValueDoesNotMatch::create(
			new MessageType('t'),
			Value::none(),
		);
		$type->addError($error2);

		self::assertTrue($type->hasErrors());
		self::assertSame([$error1, $error2], $type->getErrors());
	}

	public function testFields(): void
	{
		$type = new ArrayShapeType();

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
		$invalid1Exception = ValueDoesNotMatch::create($invalid1, Value::none());
		$type->overwriteInvalidField($key1, $invalid1Exception);

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

		self::assertSame(
			[
				$key1 => $invalid1Exception,
			],
			$type->getInvalidFields(),
		);
	}

	public function testFieldFromClosure(): void
	{
		$type = new ArrayShapeType();
		$type->addField('field', static fn (): MessageType => new MessageType('test'));

		$expectedFields = [
			'field' => new MessageType('test'),
		];

		$fields = $type->getFields();
		self::assertEquals($expectedFields, $fields);
		self::assertNotSame($expectedFields, $fields);

		$fields2 = $type->getFields();
		self::assertEquals($fields, $fields2);
		self::assertNotSame($fields, $fields2);
	}

}
