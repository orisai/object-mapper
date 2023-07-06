<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Printers;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Printers\TypeToStringConverter;
use Orisai\ObjectMapper\Printers\TypeVisualPrinter;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\CompoundTypeOperator;
use Orisai\ObjectMapper\Types\EnumType;
use Orisai\ObjectMapper\Types\GenericArrayType;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\TestType;
use PHPUnit\Framework\TestCase;

/**
 * @todo - test all options, check levels (may be not used) and separators usage (improvements)
 */
final class TypeVisualPrinterTest extends TestCase
{

	/** @var TypeVisualPrinter<string> */
	private TypeVisualPrinter $printer;

	protected function setUp(): void
	{
		$this->printer = new TypeVisualPrinter(new TypeToStringConverter());
	}

	public function testUnsupportedType(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"Unsupported type 'Orisai\ObjectMapper\Types\TestType'.",
		);

		$this->printer->printType(new TestType());
	}

	public function testMessage(): void
	{
		$type = new MessageType('test');

		self::assertSame(
			'test',
			$this->printer->printType($type),
		);
	}

	public function testSimple(): void
	{
		$type1 = new SimpleValueType('string');

		self::assertSame(
			'string',
			$this->printer->printType($type1),
		);

		$type2 = new SimpleValueType('int');
		$type2->addKeyValueParameter('first', 'value');
		$type2->addKeyParameter('second');

		self::assertSame(
			"int(first: 'value', second)",
			$this->printer->printType($type2),
		);
	}

	public function testEnum(): void
	{
		$cases = [
			'key' => 'foo',
			'key2' => 'bar',
			'key3' => 123,
			'key4' => 123.456,
			'key5' => true,
			'key6' => false,
		];
		$type = new EnumType($cases);

		self::assertSame(
			'enum(foo, bar, 123, 123.456, true, false)',
			$this->printer->printType($type),
		);
	}

	public function testArray(): void
	{
		$type1Value = new SimpleValueType('test');
		$type1Value->addKeyParameter('parameter');
		$type1 = GenericArrayType::forArray(null, $type1Value);

		self::assertSame(
			'array<test(parameter)>',
			$this->printer->printType($type1),
		);

		$type2 = GenericArrayType::forArray(new SimpleValueType('string'), new SimpleValueType('test'));

		self::assertSame(
			'array<string, test>',
			$this->printer->printType($type2),
		);

		$type3 = GenericArrayType::forArray(new SimpleValueType('string'), new SimpleValueType('test'));
		$type3->addKeyValueParameter('foo', 'bar');
		$type3->addKeyValueParameter('baz', 123);

		self::assertSame(
			"array(foo: 'bar', baz: 123)<string, test>",
			$this->printer->printType($type3),
		);

		$type4Key = new CompoundType(CompoundTypeOperator::or());
		$type4Key->addSubtype(0, new SimpleValueType('string'));
		$type4Key->addSubtype(1, new SimpleValueType('int'));
		$type4 = GenericArrayType::forArray(
			$type4Key,
			GenericArrayType::forArray(new SimpleValueType('string'), new SimpleValueType('test')),
		);

		self::assertSame(
			'string||int',
			$this->printer->printType($type4Key),
		);
		self::assertSame(
			'array<string||int, array<string, test>>',
			$this->printer->printType($type4),
		);
	}

	public function testList(): void
	{
		$type1 = GenericArrayType::forList(null, new SimpleValueType('string'));

		self::assertSame(
			'list<string>',
			$this->printer->printType($type1),
		);

		$type2 = GenericArrayType::forList(null, new SimpleValueType('string'));
		$type2->addKeyValueParameter('foo', 'bar');

		self::assertSame(
			"list(foo: 'bar')<string>",
			$this->printer->printType($type2),
		);
	}

	public function testCompound(): void
	{
		$subtype1 = new CompoundType(CompoundTypeOperator::and());
		$subtype1->addSubtype(0, new SimpleValueType('int'));
		$subtype1->addSubtype(1, new SimpleValueType('float'));

		$subtype2 = new CompoundType(CompoundTypeOperator::and());
		$subtype2->addSubtype(0, new SimpleValueType('foo'));
		$subtype2->addSubtype(1, new SimpleValueType('bar'));

		$type1 = new CompoundType(CompoundTypeOperator::or());
		$type1->addSubtype(0, $subtype1);
		$type1->addSubtype(1, $subtype2);

		self::assertSame(
			'(int&&float)||(foo&&bar)',
			$this->printer->printType($type1),
		);
	}

	public function testStructure(): void
	{
		$type1 = new MappedObjectType(MappedObject::class);
		$type1->addField('0', new SimpleValueType('t'));
		$type1->addField('a', new SimpleValueType('t'));

		self::assertSame(
			'shape{
	0: t
	a: t
}',
			$this->printer->printType($type1),
		);
	}

}
