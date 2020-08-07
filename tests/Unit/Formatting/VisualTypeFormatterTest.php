<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Formatting;

use Orisai\ObjectMapper\Formatting\VisualTypeFormatter;
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
final class VisualTypeFormatterTest extends TestCase
{

	private VisualTypeFormatter $formatter;

	protected function setUp(): void
	{
		$this->formatter = new VisualTypeFormatter();
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
			"int(first: 'value', second)",
			$this->formatter->formatType($type2),
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
		$type1 = new ArrayType(null, new SimpleValueType('test', ['parameter']));

		self::assertSame(
			'array<test(parameter)>',
			$this->formatter->formatType($type1),
		);

		$type2 = new ArrayType(new SimpleValueType('string'), new SimpleValueType('test'));

		self::assertSame(
			'array<string, test>',
			$this->formatter->formatType($type2),
		);

		$type3 = new ArrayType(new SimpleValueType('string'), new SimpleValueType('test'), ['foo' => 'bar', 'baz' => 123]);

		self::assertSame(
			"array(foo: 'bar', baz: 123)<string, test>",
			$this->formatter->formatType($type3),
		);

		$type4Key = new CompoundType(CompoundType::OPERATOR_OR);
		$type4Key->addSubtype(0, new SimpleValueType('string'));
		$type4Key->addSubtype(1, new SimpleValueType('int'));
		$type4 = new ArrayType(
			$type4Key,
			new ArrayType(new SimpleValueType('string'), new SimpleValueType('test')),
		);

		self::assertSame(
			'string|int',
			$this->formatter->formatType($type4Key),
		);
		self::assertSame(
			'array<string|int, array<string, test>>',
			$this->formatter->formatType($type4),
		);
	}

	public function testList(): void
	{
		$type1 = new ListType(new SimpleValueType('string'));

		self::assertSame(
			'list<string>',
			$this->formatter->formatType($type1),
		);

		$type2 = new ListType(new SimpleValueType('string'), ['foo' => 'bar']);

		self::assertSame(
			"list(foo: 'bar')<string>",
			$this->formatter->formatType($type2),
		);
	}

	public function testCompound(): void
	{
		//TODO - brackets
		$subtype1 = new CompoundType(CompoundType::OPERATOR_AND);
		$subtype1->addSubtype(0, new SimpleValueType('int'));
		$subtype1->addSubtype(1, new SimpleValueType('float'));

		$subtype2 = new CompoundType(CompoundType::OPERATOR_AND);
		$subtype2->addSubtype(0, new SimpleValueType('foo'));
		$subtype2->addSubtype(1, new SimpleValueType('bar'));

		$type1 = new CompoundType(CompoundType::OPERATOR_OR);
		$type1->addSubtype(0, $subtype1);
		$type1->addSubtype(1, $subtype2);

		self::assertSame(
			'int&float|foo&bar',
			$this->formatter->formatType($type1),
		);
	}

	public function testStructure(): void
	{
		$type1 = new StructureType(ValueObject::class);
		$type1->addField('0', new SimpleValueType('t'));
		$type1->addField('a', new SimpleValueType('t'));

		self::assertSame(
			'structure[
	0: t
	a: t
]',
			$this->formatter->formatType($type1),
		);
	}

}
