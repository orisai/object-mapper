<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Printers;

use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Printers\ErrorVisualPrinter;
use Orisai\ObjectMapper\Printers\TypeToStringConverter;
use Orisai\ObjectMapper\Types\ArrayType;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\EnumType;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Value;

/**
 * @extends ErrorVisualPrinterBaseTestCase<TypeToStringConverter>
 */
final class ErrorVisualPrinterToStringTest extends ErrorVisualPrinterBaseTestCase
{

	protected function setUp(): void
	{
		$this->converter = new TypeToStringConverter();
		$this->formatter = new ErrorVisualPrinter($this->converter);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideMessageType
	 */
	public function testMessage(MessageType $type): void
	{
		self::assertSame(
			'test',
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideSimpleType
	 */
	public function testSimpleValue(SimpleValueType $type): void
	{
		self::assertSame(
			'string',
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideSimpleTypeWithParameters
	 */
	public function testSimpleTypeWithParameters(SimpleValueType $type): void
	{
		self::assertSame(
			'int',
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideSimpleTypeWithInvalidParameters
	 */
	public function testSimpleTypeWithInvalidParameters(SimpleValueType $type): void
	{
		self::assertSame(
			"int(first: 'value', second)",
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideEnumType
	 */
	public function testEnum(EnumType $type): void
	{
		self::assertSame(
			'enum(foo, bar, 123, 123.456, true, false)',
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayType
	 */
	public function testArray(ArrayType $type1): void
	{
		self::assertSame(
			'array',
			$this->formatter->printType($type1),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeInvalid
	 */
	public function testArrayInvalid(ArrayType $type2): void
	{
		self::assertSame(
			'array<test(parameter)>',
			$this->formatter->printType($type2),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeSimpleInvalid
	 */
	public function testArraySimpleInvalid(ArrayType $type3): void
	{
		self::assertSame(
			'array<string, test>',
			$this->formatter->printType($type3),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeSimpleInvalidWithParameters
	 */
	public function testArraySimpleInvalidWithParameters(ArrayType $type4): void
	{
		self::assertSame(
			"array(foo: 'bar', baz: 123)<string, test>",
			$this->formatter->printType($type4),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeCompoundInvalid
	 */
	public function testArrayTypeCompoundInvalid(ArrayType $type5): void
	{
		self::assertSame(
			'array<string||int, array<string, test>>',
			$this->formatter->printType($type5),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeSimpleInvalidWithInvalidParameters
	 */
	public function testArrayTypeSimpleInvalidWithInvalidParameters(ArrayType $type): void
	{
		self::assertSame(
			"array(first: 'value', second)",
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeInvalidPairs
	 */
	public function testArrayTypeInvalidPairs(ArrayType $type): void
	{
		self::assertSame(
			"array[
	test: string => value
	0: int(second)
	123: string => int(first: 'value')
]",
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideListType
	 */
	public function testListType(ArrayType $type): void
	{
		self::assertSame(
			'list',
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideListTypeInvalid
	 */
	public function testListTypeInvalid(ArrayType $type): void
	{
		self::assertSame(
			'list<string>',
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideListTypeInvalidWithParameter
	 */
	public function testListTypeInvalidWithParameter(ArrayType $type): void
	{
		self::assertSame(
			"list(foo: 'bar')<string>",
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideListTypeInvalidWithInvalidParameter
	 */
	public function testListTypeInvalidWithInvalidParameter(ArrayType $type): void
	{
		self::assertSame(
			"list(foo: 'bar')",
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideListTypeWithInvalidValues
	 */
	public function testListTypeWithInvalidValues(ArrayType $type): void
	{
		self::assertSame(
			'list[
	0: string
	1: string
	test: string
]',
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideCompoundTypeOverwriteSubtype
	 */
	public function testCompoundTypeOverwriteSubtype(CompoundType $type): void
	{
		self::assertSame(
			'string||int',
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideCompoundTypeOverwriteSubtypeComplex
	 */
	public function testCompoundTypeOverwriteSubtypeComplex(CompoundType $type): void
	{
		//TODO - brackets
		self::assertSame(
			'int&&float||foo&&bar',
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideMappedObjectType
	 */
	public function testMappedObjectType(MappedObjectType $type): void
	{
		self::assertSame(
			'shape{}',
			$this->formatter->printType($type),
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
			$this->formatter->printType($type),
		);
		self::assertSame(
			'path > to > error > 0: t
path > to > error > a: t
path > to > error > b: shape{
	foo: t
	bar: t
}
path > to > error > Whole structure is invalid',
			$this->formatter->printError(
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
			$this->formatter->printError(InvalidData::create($type, Value::none())),
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
			$this->formatter->printType($type),
		);
		self::assertSame(
			'path -_- to -_- error -_- 0: overwritten
path -_- to -_- error -_- b: shape{
	foo: overwritten
}
path -_- to -_- error -_- Random error',
			$this->formatter->printError(
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
			$this->formatter->printError(InvalidData::create($type, Value::none())),
		);
	}

}
