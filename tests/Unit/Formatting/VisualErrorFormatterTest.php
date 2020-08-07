<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Formatting;

use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Formatting\VisualErrorFormatter;
use Orisai\ObjectMapper\Types\ArrayType;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\EnumType;
use Orisai\ObjectMapper\Types\ListType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\StructureType;
use Orisai\ObjectMapper\ValueObject;
use PHPUnit\Framework\TestCase;

/**
 * @todo - test all options, check levels (may be not used) and separators usage (improvements)
 */
final class VisualErrorFormatterTest extends TestCase
{

	private VisualErrorFormatter $formatter;

	protected function setUp(): void
	{
		$this->formatter = new VisualErrorFormatter();
	}

	public function testMessage(): void
	{
		$type = new MessageType('test');

		self::assertSame(
			'test',
			$this->formatter->formatType($type),
		);
	}

	public function testSimple(): void
	{
		$type1 = new SimpleValueType('string');

		self::assertSame(
			'string',
			$this->formatter->formatType($type1),
		);

		$type2 = new SimpleValueType('int', [
			'first' => 'value',
			'second' => true,
			'third' => false,
		]);

		self::assertSame(
			'int',
			$this->formatter->formatType($type2),
		);

		$type3 = new SimpleValueType('int', [
			'first' => 'value',
			'second' => true,
			'third' => false,
		]);
		$type3->markParameterInvalid('first');
		$type3->markParameterInvalid('second');
		$type3->markParameterInvalid('third');

		self::assertSame(
			"int(first: 'value', second)",
			$this->formatter->formatType($type3),
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
			$this->formatter->formatType($type),
		);
	}

	public function testArray(): void
	{
		//TODO - it's possible to add parameters without displayable index (only string), but it should be still possible make parameter invalid (e.g. add message during validation)
		//		- allow only string as key? 'parameter' => true
		//		- also apostrophes usage should be normalized (matter probably only for enum)
		$type1 = new ArrayType(new SimpleValueType('string'), new SimpleValueType('test'));

		self::assertSame(
			'array',
			$this->formatter->formatType($type1),
		);

		$type2 = new ArrayType(null, new SimpleValueType('test', ['parameter']));
		$type2->markInvalid();

		self::assertSame(
			'array<test(parameter)>',
			$this->formatter->formatType($type2),
		);

		$type3 = new ArrayType(new SimpleValueType('string'), new SimpleValueType('test'));
		$type3->markInvalid();

		self::assertSame(
			'array<string, test>',
			$this->formatter->formatType($type3),
		);

		$type4 = new ArrayType(new SimpleValueType('string'), new SimpleValueType('test'), ['foo' => 'bar', 'baz' => 123]);
		$type4->markInvalid();

		self::assertSame(
			"array(foo: 'bar', baz: 123)<string, test>",
			$this->formatter->formatType($type4),
		);

		$type5Key = new CompoundType(CompoundType::OPERATOR_OR);
		$type5Key->addSubtype(0, new SimpleValueType('string'));
		$type5Key->addSubtype(1, new SimpleValueType('int'));
		$type5Value = new ArrayType(new SimpleValueType('string'), new SimpleValueType('test'));
		$type5Value->markInvalid();

		$type5 = new ArrayType(
			$type5Key,
			$type5Value,
		);
		$type5->markInvalid();

		self::assertSame(
			'array<string|int, array<string, test>>',
			$this->formatter->formatType($type5),
		);

		$type5Key->overwriteInvalidSubtype(0, new SimpleValueType('string'));
		$type5Key->overwriteInvalidSubtype(1, new SimpleValueType('int'));
		self::assertSame(
			'string|int',
			$this->formatter->formatType($type5Key),
		);

		$type6 = new ArrayType(new SimpleValueType('string'), new SimpleValueType('int'), [
			'first' => 'value',
			'second' => true,
			'third' => false,
			'fourth' => true,
		]);
		$type6->markParameterInvalid('first');
		$type6->markParameterInvalid('second');

		self::assertSame(
			"array(first: 'value', second)",
			$this->formatter->formatType($type6),
		);

		$type7 = new ArrayType(new SimpleValueType('string'), new SimpleValueType('int', [
			'first' => 'value',
			'second' => true,
			'third' => false,
		]));
		$type7->addInvalidPair('test', new SimpleValueType('string'), null);
		$type7invalidValue2 = new SimpleValueType('int', [
			'first' => 'value',
			'second' => true,
			'third' => false,
		]);
		$type7invalidValue2->markParameterInvalid('second');
		$type7->addInvalidPair(0, null, $type7invalidValue2);
		$type7invalidValue3 = new SimpleValueType('int', [
			'first' => 'value',
			'second' => true,
			'third' => false,
		]);
		$type7invalidValue3->markParameterInvalid('first');
		$type7->addInvalidPair(123, new SimpleValueType('string'), $type7invalidValue3);

		self::assertSame(
			"array{
	test: string => value
	0: int(second)
	123: string => int(first: 'value')
}",
			$this->formatter->formatType($type7),
		);
	}

	public function testList(): void
	{
		$type1 = new ListType(new SimpleValueType('string'));

		self::assertSame(
			'list',
			$this->formatter->formatType($type1),
		);

		$type2 = new ListType(new SimpleValueType('string'));
		$type2->markInvalid();

		self::assertSame(
			'list<string>',
			$this->formatter->formatType($type2),
		);

		$type3 = new ListType(new SimpleValueType('string'), ['foo' => 'bar']);
		$type3->markInvalid();

		self::assertSame(
			"list(foo: 'bar')<string>",
			$this->formatter->formatType($type3),
		);

		$type4 = new ListType(new SimpleValueType('string'), ['foo' => 'bar']);
		$type4->markParameterInvalid('foo');

		self::assertSame(
			"list(foo: 'bar')",
			$this->formatter->formatType($type4),
		);

		$type5 = new ListType(new SimpleValueType('string'));
		$type5->addInvalidItem(0, new SimpleValueType('string'));
		$type5->addInvalidItem(1, new SimpleValueType('string'));
		$type5->addInvalidItem('test', new SimpleValueType('string'));

		self::assertSame(
			'list{
	0: string
	1: string
	test: string
}',
			$this->formatter->formatType($type5),
		);
	}

	public function testCompound(): void
	{
		//TODO - brackets
		$subtype1 = new CompoundType(CompoundType::OPERATOR_AND);
		$subtype1->addSubtype(0, new SimpleValueType('int'));
		$subtype1->overwriteInvalidSubtype(0, new SimpleValueType('int'));
		$subtype1->addSubtype(1, new SimpleValueType('float'));
		$subtype1->overwriteInvalidSubtype(1, new SimpleValueType('float'));

		$subtype2 = new CompoundType(CompoundType::OPERATOR_AND);
		$subtype2->addSubtype(0, new SimpleValueType('foo'));
		$subtype2->overwriteInvalidSubtype(0, new SimpleValueType('foo'));
		$subtype2->addSubtype(1, new SimpleValueType('bar'));
		$subtype2->overwriteInvalidSubtype(1, new SimpleValueType('bar'));

		$type1 = new CompoundType(CompoundType::OPERATOR_OR);
		$type1->addSubtype(0, $subtype1);
		$type1->overwriteInvalidSubtype(0, $subtype1);
		$type1->addSubtype(1, $subtype2);
		$type1->overwriteInvalidSubtype(1, $subtype2);

		self::assertSame(
			'int&float|foo&bar',
			$this->formatter->formatType($type1),
		);
	}

	public function testStructureValid(): void
	{
		$type1 = new StructureType(ValueObject::class);
		$type1->addField('0', new SimpleValueType('t'));
		$type1->addField('a', new SimpleValueType('t'));

		self::assertSame(
			'structure[

]',
			$this->formatter->formatType($type1),
		);
	}

	public function testStructureInvalid(): void
	{
		$fieldType1 = new StructureType(ValueObject::class);
		$fieldType1->addField('foo', new SimpleValueType('t'));
		$fieldType1->addField('bar', new SimpleValueType('t'));

		$type1 = new StructureType(ValueObject::class);
		$type1->addField('0', new SimpleValueType('t'));
		$type1->addField('a', new SimpleValueType('t'));
		$type1->addField('b', $fieldType1);
		$type1->addError(new MessageType('Whole structure is invalid'));
		$type1->markInvalid();

		self::assertSame(
			'structure[
	0: t
	a: t
	b: structure[
		foo: t
		bar: t
	]
	Whole structure is invalid
]',
			$this->formatter->formatType($type1),
		);
		self::assertSame(
			'path > to > error > 0: t
path > to > error > a: t
path > to > error > b: structure[
	foo: t
	bar: t
]
path > to > error > Whole structure is invalid',
			$this->formatter->formatError(InvalidData::create($type1), ['path', 'to', 'error']),
		);
		self::assertSame(
			'0: t
a: t
b: structure[
	foo: t
	bar: t
]
Whole structure is invalid',
			$this->formatter->formatError(InvalidData::create($type1)),
		);
	}

	public function testStructureFieldsInvalid(): void
	{
		$fieldType1 = new StructureType(ValueObject::class);
		$fieldType1->addField('foo', new SimpleValueType('t'));
		$fieldType1->addField('bar', new SimpleValueType('t'));

		$fieldType1Invalid = new StructureType(ValueObject::class);
		$fieldType1Invalid->addField('foo', new SimpleValueType('t'));
		$fieldType1Invalid->addField('bar', new SimpleValueType('t'));
		$fieldType1Invalid->overwriteInvalidField('foo', new SimpleValueType('overwritten'));

		$type1 = new StructureType(ValueObject::class);
		$type1->addField('0', new SimpleValueType('t'));
		$type1->addField('a', new SimpleValueType('t'));
		$type1->addField('b', $fieldType1);
		$type1->addError(new MessageType('Random error'));
		$type1->overwriteInvalidField('0', new SimpleValueType('overwritten'));
		$type1->overwriteInvalidField('b', $fieldType1Invalid);

		$this->formatter->pathNodeSeparator = ' -_- ';

		self::assertSame(
			'structure[
	0: overwritten
	b: structure[
		foo: overwritten
	]
	Random error
]',
			$this->formatter->formatType($type1),
		);
		self::assertSame(
			'path -_- to -_- error -_- 0: overwritten
path -_- to -_- error -_- b: structure[
	foo: overwritten
]
path -_- to -_- error -_- Random error',
			$this->formatter->formatError(InvalidData::create($type1), ['path', 'to', 'error']),
		);
		self::assertSame(
			'0: overwritten
b: structure[
	foo: overwritten
]
Random error',
			$this->formatter->formatError(InvalidData::create($type1)),
		);
	}

}
