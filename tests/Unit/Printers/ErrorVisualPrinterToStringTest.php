<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Printers;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Printers\ErrorVisualPrinter;
use Orisai\ObjectMapper\Printers\TypeToStringConverter;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\EnumType;
use Orisai\ObjectMapper\Types\GenericArrayType;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\TestType;

final class ErrorVisualPrinterToStringTest extends ErrorVisualPrinterBaseTestCase
{

	private TypeToStringConverter $converter;

	/** @var ErrorVisualPrinter<string> */
	private ErrorVisualPrinter $printer;

	protected function setUp(): void
	{
		$this->converter = new TypeToStringConverter();
		$this->printer = new ErrorVisualPrinter($this->converter);
	}

	public function testUnsupportedType(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"Unsupported type 'Orisai\ObjectMapper\Types\TestType'.",
		);

		$this->printer->printType(new TestType());
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideMessageType
	 */
	public function testMessage(MessageType $type): void
	{
		self::assertSame(
			'test',
			$this->printer->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideSimpleType
	 */
	public function testSimpleValue(SimpleValueType $type): void
	{
		self::assertSame(
			'string',
			$this->printer->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideSimpleTypeWithParameters
	 */
	public function testSimpleTypeWithParameters(SimpleValueType $type): void
	{
		self::assertSame(
			'int',
			$this->printer->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideSimpleTypeWithInvalidParameters
	 */
	public function testSimpleTypeWithInvalidParameters(SimpleValueType $type): void
	{
		self::assertSame(
			"int(first: 'value', second)",
			$this->printer->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideEnumType
	 */
	public function testEnum(EnumType $type): void
	{
		self::assertSame(
			'enum(foo, bar, 123, 123.456, true, false)',
			$this->printer->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayType
	 */
	public function testArray(GenericArrayType $type1): void
	{
		self::assertSame(
			'array',
			$this->printer->printType($type1),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeInvalid
	 */
	public function testArrayInvalid(GenericArrayType $type2): void
	{
		self::assertSame(
			'array<test(parameter)>',
			$this->printer->printType($type2),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeSimpleInvalid
	 */
	public function testArraySimpleInvalid(GenericArrayType $type3): void
	{
		self::assertSame(
			'array<string, test>',
			$this->printer->printType($type3),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeSimpleInvalidWithParameters
	 */
	public function testArraySimpleInvalidWithParameters(GenericArrayType $type4): void
	{
		self::assertSame(
			"array(foo: 'bar', baz: 123)<string, test>",
			$this->printer->printType($type4),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeCompoundInvalid
	 */
	public function testArrayTypeCompoundInvalid(GenericArrayType $type5): void
	{
		self::assertSame(
			'array<string||int, array<string, test>>',
			$this->printer->printType($type5),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeSimpleInvalidWithInvalidParameters
	 */
	public function testArrayTypeSimpleInvalidWithInvalidParameters(GenericArrayType $type): void
	{
		self::assertSame(
			"array(first: 'value', second)",
			$this->printer->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeInvalidPairs
	 */
	public function testArrayTypeInvalidPairs(GenericArrayType $type): void
	{
		self::assertSame(
			"array[
	test: string => value
	0: int(second)
	123: string => int(first: 'value')
]",
			$this->printer->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideListType
	 */
	public function testListType(GenericArrayType $type): void
	{
		self::assertSame(
			'list',
			$this->printer->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideListTypeInvalid
	 */
	public function testListTypeInvalid(GenericArrayType $type): void
	{
		self::assertSame(
			'list<string>',
			$this->printer->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideListTypeInvalidWithParameter
	 */
	public function testListTypeInvalidWithParameter(GenericArrayType $type): void
	{
		self::assertSame(
			"list(foo: 'bar')<string>",
			$this->printer->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideListTypeInvalidWithInvalidParameter
	 */
	public function testListTypeInvalidWithInvalidParameter(GenericArrayType $type): void
	{
		self::assertSame(
			"list(foo: 'bar')",
			$this->printer->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideListTypeWithInvalidValues
	 */
	public function testListTypeWithInvalidValues(GenericArrayType $type): void
	{
		self::assertSame(
			'list[
	0: string
	1: string
	test: string
]',
			$this->printer->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideCompoundTypeOverwriteSubtype
	 */
	public function testCompoundTypeOverwriteSubtype(CompoundType $type): void
	{
		self::assertSame(
			'string||int',
			$this->printer->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideCompoundTypeOverwriteSubtypeComplex
	 */
	public function testCompoundTypeOverwriteSubtypeComplex(CompoundType $type): void
	{
		self::assertSame(
			'(int&&float)||(foo&&bar)',
			$this->printer->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideMappedObjectType
	 */
	public function testMappedObjectType(MappedObjectType $type): void
	{
		self::assertSame(
			'shape{}',
			$this->printer->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideMappedObjectTypeInvalid
	 */
	public function testMappedObjectTypeInvalid(MappedObjectType $type): void
	{
		self::assertSame(
			'shape{
	0: t
	a: t
	b: shape{
		foo: t
		bar: t
	}
	Whole structure is invalid
}',
			$this->printer->printType($type),
		);
		self::assertSame(
			'path > to > error > 0: t
path > to > error > a: t
path > to > error > b: shape{
	foo: t
	bar: t
}
path > to > error > Whole structure is invalid',
			$this->printer->printError(
				InvalidData::create($type, Value::none()),
				['path', 'to', 'error'],
			),
		);
		self::assertSame(
			'0: t
a: t
b: shape{
	foo: t
	bar: t
}
Whole structure is invalid',
			$this->printer->printError(InvalidData::create($type, Value::none())),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideMappedObjectTypeInvalidWithInvalidFields
	 */
	public function testMappedObjectTypeInvalidWithInvalidFields(MappedObjectType $type): void
	{
		$this->converter->pathNodeSeparator = ' -_- ';

		self::assertSame(
			'shape{
	0: overwritten
	b: shape{
		foo: overwritten
	}
	Random error
}',
			$this->printer->printType($type),
		);
		self::assertSame(
			'path -_- to -_- error -_- 0: overwritten
path -_- to -_- error -_- b: shape{
	foo: overwritten
}
path -_- to -_- error -_- Random error',
			$this->printer->printError(
				InvalidData::create($type, Value::none()),
				['path', 'to', 'error'],
			),
		);
		self::assertSame(
			'0: overwritten
b: shape{
	foo: overwritten
}
Random error',
			$this->printer->printError(InvalidData::create($type, Value::none())),
		);
	}

}
