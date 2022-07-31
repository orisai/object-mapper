<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Printers;

use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Printers\ErrorVisualPrinter;
use Orisai\ObjectMapper\Types\ArrayType;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\EnumType;
use Orisai\ObjectMapper\Types\ListType;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Value;
use PHPUnit\Framework\TestCase;

/**
 * @todo - test all options, check levels (may be not used) and separators usage (improvements)
 */
final class ErrorVisualPrinterTest extends TestCase
{

	private ErrorVisualPrinter $formatter;

	protected function setUp(): void
	{
		$this->formatter = new ErrorVisualPrinter();
	}

	public function testMessage(): void
	{
		$type = new MessageType('test');

		self::assertSame(
			'test',
			$this->formatter->printType($type),
		);
	}

	public function testSimple(): void
	{
		$type1 = new SimpleValueType('string');

		self::assertSame(
			'string',
			$this->formatter->printType($type1),
		);

		$type2 = new SimpleValueType('int');
		$type2->addKeyValueParameter('first', 'value');
		$type2->addKeyParameter('second');

		self::assertSame(
			'int',
			$this->formatter->printType($type2),
		);

		$type3 = new SimpleValueType('int');
		$type3->addKeyValueParameter('first', 'value');
		$type3->addKeyParameter('second');
		$type3->markParameterInvalid('first');
		$type3->markParameterInvalid('second');

		self::assertSame(
			"int(first: 'value', second)",
			$this->formatter->printType($type3),
		);
	}

	public function testEnum(): void
	{
		$values = [
			'key' => 'foo',
			'key2' => 'bar',
			'key3' => 123,
			'key4' => 123.456,
			'key5' => true,
			'key6' => false,
		];
		$type = new EnumType($values);

		self::assertSame(
			'enum(foo, bar, 123, 123.456, true, false)',
			$this->formatter->printType($type),
		);
	}

	public function testArray(): void
	{
		$type1 = new ArrayType(new SimpleValueType('string'), new SimpleValueType('test'));

		self::assertSame(
			'array',
			$this->formatter->printType($type1),
		);

		$type2Value = new SimpleValueType('test');
		$type2Value->addKeyParameter('parameter');
		$type2 = new ArrayType(null, $type2Value);
		$type2->markInvalid();

		self::assertSame(
			'array<test(parameter)>',
			$this->formatter->printType($type2),
		);

		$type3 = new ArrayType(new SimpleValueType('string'), new SimpleValueType('test'));
		$type3->markInvalid();

		self::assertSame(
			'array<string, test>',
			$this->formatter->printType($type3),
		);

		$type4 = new ArrayType(new SimpleValueType('string'), new SimpleValueType('test'));
		$type4->addKeyValueParameter('foo', 'bar');
		$type4->addKeyValueParameter('baz', 123);
		$type4->markInvalid();

		self::assertSame(
			"array(foo: 'bar', baz: 123)<string, test>",
			$this->formatter->printType($type4),
		);

		$type5Key = CompoundType::createOrType();
		$type5Key->addSubtype(0, new SimpleValueType('string'));
		$type5Key->addSubtype(1, new SimpleValueType('int'));
		$type5Value = new ArrayType(new SimpleValueType('string'), new SimpleValueType('test'));
		$type5Value->markInvalid();

		$type5 = new ArrayType($type5Key, $type5Value);
		$type5->markInvalid();

		self::assertSame(
			'array<string|int, array<string, test>>',
			$this->formatter->printType($type5),
		);

		$type5Key->overwriteInvalidSubtype(
			0,
			ValueDoesNotMatch::create(new SimpleValueType('string'), Value::none()),
		);
		$type5Key->overwriteInvalidSubtype(
			1,
			ValueDoesNotMatch::create(new SimpleValueType('int'), Value::none()),
		);
		self::assertSame(
			'string|int',
			$this->formatter->printType($type5Key),
		);

		$type6 = new ArrayType(new SimpleValueType('string'), new SimpleValueType('int'));
		$type6->addKeyValueParameter('first', 'value');
		$type6->addKeyParameter('second');
		$type6->addKeyParameter('third');
		$type6->markParameterInvalid('first');
		$type6->markParameterInvalid('second');

		self::assertSame(
			"array(first: 'value', second)",
			$this->formatter->printType($type6),
		);

		$type7 = new ArrayType(new SimpleValueType('string'), new SimpleValueType('int'));
		$type7->addKeyValueParameter('first', 'value');
		$type7->addKeyParameter('second');
		$type7->addKeyParameter('third');
		$type7->addInvalidPair(
			'test',
			ValueDoesNotMatch::create(
				new SimpleValueType('string'),
				Value::of(null),
			),
			null,
		);
		$type7invalidValue2 = new SimpleValueType('int');
		$type7invalidValue2->addKeyValueParameter('first', 'value');
		$type7invalidValue2->addKeyParameter('second');
		$type7invalidValue2->addKeyParameter('third');
		$type7invalidValue2->markParameterInvalid('second');
		$type7->addInvalidPair(
			0,
			null,
			ValueDoesNotMatch::create($type7invalidValue2, Value::none()),
		);
		$type7invalidValue3 = new SimpleValueType('int');
		$type7invalidValue3->addKeyValueParameter('first', 'value');
		$type7invalidValue3->addKeyParameter('second');
		$type7invalidValue3->addKeyParameter('third');
		$type7invalidValue3->markParameterInvalid('first');
		$type7->addInvalidPair(
			123,
			ValueDoesNotMatch::create(new SimpleValueType('string'), Value::none()),
			ValueDoesNotMatch::create($type7invalidValue3, Value::none()),
		);

		self::assertSame(
			"array[
	test: string => value
	0: int(second)
	123: string => int(first: 'value')
]",
			$this->formatter->printType($type7),
		);
	}

	public function testList(): void
	{
		$type1 = new ListType(new SimpleValueType('string'));

		self::assertSame(
			'list',
			$this->formatter->printType($type1),
		);

		$type2 = new ListType(new SimpleValueType('string'));
		$type2->markInvalid();

		self::assertSame(
			'list<string>',
			$this->formatter->printType($type2),
		);

		$type3 = new ListType(new SimpleValueType('string'));
		$type3->addKeyValueParameter('foo', 'bar');
		$type3->markInvalid();

		self::assertSame(
			"list(foo: 'bar')<string>",
			$this->formatter->printType($type3),
		);

		$type4 = new ListType(new SimpleValueType('string'));
		$type4->addKeyValueParameter('foo', 'bar');
		$type4->markParameterInvalid('foo');

		self::assertSame(
			"list(foo: 'bar')",
			$this->formatter->printType($type4),
		);

		$type5 = new ListType(new SimpleValueType('string'));
		$type5->addInvalidItem(
			0,
			ValueDoesNotMatch::create(new SimpleValueType('string'), Value::none()),
		);
		$type5->addInvalidItem(
			1,
			ValueDoesNotMatch::create(new SimpleValueType('string'), Value::none()),
		);
		$type5->addInvalidItem(
			'test',
			ValueDoesNotMatch::create(new SimpleValueType('string'), Value::none()),
		);

		self::assertSame(
			'list[
	0: string
	1: string
	test: string
]',
			$this->formatter->printType($type5),
		);
	}

	public function testCompound(): void
	{
		//TODO - brackets
		$subtype1 = CompoundType::createAndType();
		$subtype1->addSubtype(0, new SimpleValueType('int'));
		$subtype1->overwriteInvalidSubtype(
			0,
			ValueDoesNotMatch::create(new SimpleValueType('int'), Value::none()),
		);
		$subtype1->addSubtype(1, new SimpleValueType('float'));
		$subtype1->overwriteInvalidSubtype(
			1,
			ValueDoesNotMatch::create(new SimpleValueType('float'), Value::none()),
		);

		$subtype2 = CompoundType::createAndType();
		$subtype2->addSubtype(0, new SimpleValueType('foo'));
		$subtype2->overwriteInvalidSubtype(
			0,
			ValueDoesNotMatch::create(new SimpleValueType('foo'), Value::none()),
		);
		$subtype2->addSubtype(1, new SimpleValueType('bar'));
		$subtype2->overwriteInvalidSubtype(
			1,
			ValueDoesNotMatch::create(new SimpleValueType('bar'), Value::none()),
		);

		$type1 = CompoundType::createOrType();
		$type1->addSubtype(0, $subtype1);
		$type1->overwriteInvalidSubtype(
			0,
			ValueDoesNotMatch::create($subtype1, Value::none()),
		);
		$type1->addSubtype(1, $subtype2);
		$type1->overwriteInvalidSubtype(
			1,
			ValueDoesNotMatch::create($subtype2, Value::none()),
		);

		self::assertSame(
			'int&float|foo&bar',
			$this->formatter->printType($type1),
		);
	}

	public function testStructureValid(): void
	{
		$type1 = new MappedObjectType(MappedObject::class);
		$type1->addField('0', new SimpleValueType('t'));
		$type1->addField('a', new SimpleValueType('t'));

		self::assertSame(
			'shape{}',
			$this->formatter->printType($type1),
		);
	}

	public function testStructureInvalid(): void
	{
		$fieldType1 = new MappedObjectType(MappedObject::class);
		$fieldType1->addField('foo', new SimpleValueType('t'));
		$fieldType1->addField('bar', new SimpleValueType('t'));

		$type1 = new MappedObjectType(MappedObject::class);
		$type1->addField('0', new SimpleValueType('t'));
		$type1->addField('a', new SimpleValueType('t'));
		$type1->addField('b', $fieldType1);
		$type1->addError(ValueDoesNotMatch::create(
			new MessageType('Whole structure is invalid'),
			Value::none(),
		));
		$type1->markInvalid();

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
			$this->formatter->printType($type1),
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
				InvalidData::create($type1, Value::none()),
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
			$this->formatter->printError(InvalidData::create($type1, Value::none())),
		);
	}

	public function testStructureFieldsInvalid(): void
	{
		$fieldType1 = new MappedObjectType(MappedObject::class);
		$fieldType1->addField('foo', new SimpleValueType('t'));
		$fieldType1->addField('bar', new SimpleValueType('t'));

		$fieldType1Invalid = new MappedObjectType(MappedObject::class);
		$fieldType1Invalid->addField('foo', new SimpleValueType('t'));
		$fieldType1Invalid->addField('bar', new SimpleValueType('t'));
		$fieldType1Invalid->overwriteInvalidField(
			'foo',
			ValueDoesNotMatch::create(new SimpleValueType('overwritten'), Value::none()),
		);

		$type1 = new MappedObjectType(MappedObject::class);
		$type1->addField('0', new SimpleValueType('t'));
		$type1->addField('a', new SimpleValueType('t'));
		$type1->addField('b', $fieldType1);
		$type1->addError(ValueDoesNotMatch::create(
			new MessageType('Random error'),
			Value::none(),
		));
		$type1->overwriteInvalidField(
			'0',
			ValueDoesNotMatch::create(new SimpleValueType('overwritten'), Value::none()),
		);
		$type1->overwriteInvalidField(
			'b',
			ValueDoesNotMatch::create($fieldType1Invalid, Value::none()),
		);

		$this->formatter->pathNodeSeparator = ' -_- ';

		self::assertSame(
			'shape{
	0: overwritten
	b: shape{
		foo: overwritten
	}
	Random error
}',
			$this->formatter->printType($type1),
		);
		self::assertSame(
			'path -_- to -_- error -_- 0: overwritten
path -_- to -_- error -_- b: shape{
	foo: overwritten
}
path -_- to -_- error -_- Random error',
			$this->formatter->printError(
				InvalidData::create($type1, Value::none()),
				['path', 'to', 'error'],
			),
		);
		self::assertSame(
			'0: overwritten
b: shape{
	foo: overwritten
}
Random error',
			$this->formatter->printError(InvalidData::create($type1, Value::none())),
		);
	}

}
