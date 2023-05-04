<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Tester;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\CompoundTypeOperator;
use Orisai\ObjectMapper\Types\EnumType;
use Orisai\ObjectMapper\Types\GenericArrayType;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\SimpleValueType;

final class TypesTestProvider
{

	public static function provideMessageType(): Generator
	{
		yield [new MessageType('test')];
	}

	public static function provideSimpleType(): Generator
	{
		$type = new SimpleValueType('string');

		yield [$type];
	}

	public static function provideSimpleTypeWithParameters(): Generator
	{
		$type = new SimpleValueType('int');
		$type->addKeyValueParameter('first', 'value');
		$type->addKeyParameter('second');

		yield [$type];
	}

	public static function provideSimpleTypeWithInvalidParameters(): Generator
	{
		$type = new SimpleValueType('int');
		$type->addKeyValueParameter('first', 'value');
		$type->addKeyParameter('second');
		$type->markParameterInvalid('first');
		$type->markParameterInvalid('second');

		yield [$type];
	}

	public static function provideEnumType(): Generator
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

		yield [$type];
	}

	public static function provideArrayType(): Generator
	{
		$type = GenericArrayType::forArray(new SimpleValueType('string'), new SimpleValueType('test'));

		yield [$type];
	}

	public static function provideArrayTypeInvalid(): Generator
	{
		$typeValue = new SimpleValueType('test');
		$typeValue->addKeyParameter('parameter');
		$type = GenericArrayType::forArray(null, $typeValue);
		$type->markInvalid();

		yield [$type];
	}

	public static function provideArrayTypeSimpleInvalid(): Generator
	{
		$type = GenericArrayType::forArray(new SimpleValueType('string'), new SimpleValueType('test'));
		$type->markInvalid();

		yield [$type];
	}

	public static function provideArrayTypeSimpleInvalidWithParameters(): Generator
	{
		$type = GenericArrayType::forArray(new SimpleValueType('string'), new SimpleValueType('test'));
		$type->addKeyValueParameter('foo', 'bar');
		$type->addKeyValueParameter('baz', 123);
		$type->markInvalid();

		yield [$type];
	}

	public static function provideArrayTypeSimpleInvalidWithInvalidParameters(): Generator
	{
		$type = GenericArrayType::forArray(new SimpleValueType('string'), new SimpleValueType('int'));
		$type->addKeyValueParameter('first', 'value');
		$type->addKeyParameter('second');
		$type->addKeyParameter('third');
		$type->markParameterInvalid('first');
		$type->markParameterInvalid('second');

		yield [$type];
	}

	public static function provideArrayTypeCompoundInvalid(): Generator
	{
		$typeKey = new CompoundType(CompoundTypeOperator::or());
		$typeKey->addSubtype(0, new SimpleValueType('string'));
		$typeKey->addSubtype(1, new SimpleValueType('int'));
		$typeValue = GenericArrayType::forArray(new SimpleValueType('string'), new SimpleValueType('test'));
		$typeValue->markInvalid();

		$type = GenericArrayType::forArray($typeKey, $typeValue);
		$type->markInvalid();

		yield [$type];
	}

	public static function provideArrayTypeInvalidPairs(): Generator
	{
		$type = GenericArrayType::forArray(new SimpleValueType('string'), new SimpleValueType('int'));
		$type->addKeyValueParameter('first', 'value');
		$type->addKeyParameter('second');
		$type->addKeyParameter('third');
		$type->addInvalidPair(
			'test',
			ValueDoesNotMatch::create(
				new SimpleValueType('string'),
				Value::of(null),
			),
			null,
		);
		$typeInvalidValue1 = new SimpleValueType('int');
		$typeInvalidValue1->addKeyValueParameter('first', 'value');
		$typeInvalidValue1->addKeyParameter('second');
		$typeInvalidValue1->addKeyParameter('third');
		$typeInvalidValue1->markParameterInvalid('second');
		$type->addInvalidPair(
			0,
			null,
			ValueDoesNotMatch::create($typeInvalidValue1, Value::none()),
		);
		$typeInvalidValue2 = new SimpleValueType('int');
		$typeInvalidValue2->addKeyValueParameter('first', 'value');
		$typeInvalidValue2->addKeyParameter('second');
		$typeInvalidValue2->addKeyParameter('third');
		$typeInvalidValue2->markParameterInvalid('first');
		$type->addInvalidPair(
			123,
			ValueDoesNotMatch::create(new SimpleValueType('string'), Value::none()),
			ValueDoesNotMatch::create($typeInvalidValue2, Value::none()),
		);

		yield [$type];
	}

	public static function provideListType(): Generator
	{
		$type = GenericArrayType::forList(null, new SimpleValueType('string'));

		yield [$type];
	}

	public static function provideListTypeInvalid(): Generator
	{
		$type = GenericArrayType::forList(null, new SimpleValueType('string'));
		$type->markInvalid();

		yield [$type];
	}

	public static function provideListTypeInvalidWithParameter(): Generator
	{
		$type = GenericArrayType::forList(null, new SimpleValueType('string'));
		$type->addKeyValueParameter('foo', 'bar');
		$type->markInvalid();

		yield [$type];
	}

	public static function provideListTypeInvalidWithInvalidParameter(): Generator
	{
		$type = GenericArrayType::forList(null, new SimpleValueType('string'));
		$type->addKeyValueParameter('foo', 'bar');
		$type->markParameterInvalid('foo');

		yield [$type];
	}

	public static function provideListTypeWithInvalidValues(): Generator
	{
		$type = GenericArrayType::forList(null, new SimpleValueType('string'));
		$type->addInvalidValue(
			0,
			ValueDoesNotMatch::create(new SimpleValueType('string'), Value::none()),
		);
		$type->addInvalidValue(
			1,
			ValueDoesNotMatch::create(new SimpleValueType('string'), Value::none()),
		);
		$type->addInvalidValue(
			'test',
			ValueDoesNotMatch::create(new SimpleValueType('string'), Value::none()),
		);

		yield [$type];
	}

	public static function provideCompoundTypeOverwriteSubtype(): Generator
	{
		$type = new CompoundType(CompoundTypeOperator::or());
		$type->addSubtype(0, new SimpleValueType('foo'));
		$type->addSubtype(1, new SimpleValueType('bar'));

		$type->overwriteInvalidSubtype(
			0,
			ValueDoesNotMatch::create(new SimpleValueType('string'), Value::none()),
		);
		$type->overwriteInvalidSubtype(
			1,
			ValueDoesNotMatch::create(new SimpleValueType('int'), Value::none()),
		);

		yield [$type];
	}

	public static function provideCompoundTypeOverwriteSubtypeComplex(): Generator
	{
		$subtype1 = new CompoundType(CompoundTypeOperator::and());
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

		$subtype2 = new CompoundType(CompoundTypeOperator::and());
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

		$type = new CompoundType(CompoundTypeOperator::or());
		$type->addSubtype(0, $subtype1);
		$type->overwriteInvalidSubtype(
			0,
			ValueDoesNotMatch::create($subtype1, Value::none()),
		);
		$type->addSubtype(1, $subtype2);
		$type->overwriteInvalidSubtype(
			1,
			ValueDoesNotMatch::create($subtype2, Value::none()),
		);

		yield [$type];
	}

	public static function provideMappedObjectType(): Generator
	{
		$type = new MappedObjectType(MappedObject::class);
		$type->addField('0', new SimpleValueType('t'));
		$type->addField('a', new SimpleValueType('t'));

		yield [$type];
	}

	public static function provideMappedObjectTypeInvalid(): Generator
	{
		$fieldType1 = new MappedObjectType(MappedObject::class);
		$fieldType1->addField('foo', new SimpleValueType('t'));
		$fieldType1->addField('bar', new SimpleValueType('t'));

		$type = new MappedObjectType(MappedObject::class);
		$type->addField('0', new SimpleValueType('t'));
		$type->addField('a', new SimpleValueType('t'));
		$type->addField('b', $fieldType1);
		$type->addError(ValueDoesNotMatch::create(
			new MessageType('Whole structure is invalid'),
			Value::none(),
		));
		$type->markInvalid();

		yield [$type];
	}

	public static function provideMappedObjectTypeInvalidWithInvalidFields(): Generator
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

		$type = new MappedObjectType(MappedObject::class);
		$type->addField('0', new SimpleValueType('t'));
		$type->addField('a', new SimpleValueType('t'));
		$type->addField('b', $fieldType1);
		$type->addError(ValueDoesNotMatch::create(
			new MessageType('Random error'),
			Value::none(),
		));
		$type->overwriteInvalidField(
			'0',
			ValueDoesNotMatch::create(new SimpleValueType('overwritten'), Value::none()),
		);
		$type->overwriteInvalidField(
			'b',
			ValueDoesNotMatch::create($fieldType1Invalid, Value::none()),
		);

		yield [$type];
	}

}
