<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\GenericArrayType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Value;
use PHPUnit\Framework\TestCase;

final class CompoundTypeTest extends TestCase
{

	public function testOperator(): void
	{
		$type = CompoundType::createAndType();

		self::assertSame(CompoundType::OperatorAnd, $type->getOperator());
		self::assertSame([], $type->getSubtypes());
	}

	public function testSubtypes(): void
	{
		$type = CompoundType::createOrType();

		self::assertSame(CompoundType::OperatorOr, $type->getOperator());
		self::assertSame([], $type->getSubtypes());

		$key1 = 1;
		$subtype1 = new SimpleValueType('int');
		$type->addSubtype(1, $subtype1);

		$key2 = 2;
		$subtype2 = new SimpleValueType('string');
		$type->addSubtype(2, $subtype2);

		$key3 = 3;
		$subtype3 = GenericArrayType::forArray(null, new SimpleValueType('string'));
		$type->addSubtype(3, $subtype3);

		self::assertSame(
			[
				$key1 => $subtype1,
				$key2 => $subtype2,
				$key3 => $subtype3,
			],
			$type->getSubtypes(),
		);

		$invalid1 = new SimpleValueType('int');
		self::assertTrue($type->isSubtypeValid($key1));
		self::assertFalse($type->isSubtypeInvalid($key1));

		$invalid1Exception = ValueDoesNotMatch::create($invalid1, Value::none());
		$type->overwriteInvalidSubtype($key1, $invalid1Exception);
		self::assertFalse($type->isSubtypeValid($key1));
		self::assertTrue($type->isSubtypeInvalid($key1));
		self::assertSame($invalid1, $type->getSubtypes()[$key1]);

		self::assertSame(
			[
				$key1 => $invalid1,
				$key2 => $subtype2,
				$key3 => $subtype3,
			],
			$type->getSubtypes(),
		);

		self::assertSame(
			[
				$key1 => $invalid1Exception,
			],
			$type->getInvalidSubtypes(),
		);

		self::assertFalse($type->isSubtypeSkipped($key2));
		self::assertTrue($type->isSubtypeValid($key2));
		$type->setSubtypeSkipped($key2);
		self::assertTrue($type->isSubtypeSkipped($key2));
		self::assertFalse($type->isSubtypeValid($key2));
	}

	public function testAlreadySet(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage('Cannot set subtype with key 1 because it was already set');

		$type = CompoundType::createAndType();
		$type->addSubtype(1, new MessageType('t'));
		$type->addSubtype(1, new MessageType('t'));
	}

	public function testNeverSetSkipped(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage('Cannot mark subtype with key 1 skipped because it was never set');

		$type = CompoundType::createAndType();
		$type->setSubtypeSkipped(1);
	}

	public function testNeverSetOverwrite(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			'Cannot overwrite subtype with key 1 with invalid subtype because it was never set',
		);

		$type = CompoundType::createAndType();
		$type->overwriteInvalidSubtype(
			1,
			ValueDoesNotMatch::create(new MessageType('f'), Value::none()),
		);
	}

	public function testIsSkippedAndCannotBeOverwritten(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage('Cannot overwrite subtype with key 1 because it is already marked as skipped');

		$type = CompoundType::createAndType();
		$type->addSubtype(1, new MessageType('t'));
		$type->setSubtypeSkipped(1);
		$type->overwriteInvalidSubtype(
			1,
			ValueDoesNotMatch::create(new MessageType('t'), Value::none()),
		);
	}

	public function testIsOverwrittenAndCannotBeSkipped(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			'Cannot mark subtype with key 1 skipped because it was already overwritten with invalid subtype',
		);

		$type = CompoundType::createAndType();
		$type->addSubtype(1, new MessageType('t'));
		$type->overwriteInvalidSubtype(
			1,
			ValueDoesNotMatch::create(new MessageType('t'), Value::none()),
		);
		$type->setSubtypeSkipped(1);
	}

}
