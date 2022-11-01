<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Printers;

use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Printers\ErrorVisualPrinter;
use Orisai\ObjectMapper\Printers\TypeToArrayConverter;
use Orisai\ObjectMapper\Types\ArrayType;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\EnumType;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Value;

/**
 * @extends ErrorVisualPrinterBaseTestCase<TypeToArrayConverter>
 */
final class ErrorVisualPrinterToArrayTest extends ErrorVisualPrinterBaseTestCase
{

	protected function setUp(): void
	{
		$this->converter = new TypeToArrayConverter();
		$this->formatter = new ErrorVisualPrinter($this->converter);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideMessageType
	 */
	public function testMessage(MessageType $type): void
	{
		self::assertSame(
			['type' => 'message', 'message' => 'test'],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideSimpleType
	 */
	public function testSimpleValue(SimpleValueType $type): void
	{
		$type1 = new SimpleValueType('string');

		self::assertSame(
			['type' => 'simple', 'name' => 'string', 'parameters' => []],
			$this->formatter->printType($type1),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideSimpleTypeWithParameters
	 */
	public function testSimpleTypeWithParameters(SimpleValueType $type): void
	{
		self::assertSame(
			['type' => 'simple', 'name' => 'int', 'parameters' => []],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideSimpleTypeWithInvalidParameters
	 */
	public function testSimpleTypeWithInvalidParameters(SimpleValueType $type): void
	{
		self::assertSame(
			[
				'type' => 'simple', 'name' => 'int',
				'parameters' => [
					[
						'key' => 'first',
						'value' => 'value',
					],
					[
						'key' => 'second',
					],
				],
			],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideEnumType
	 */
	public function testEnum(EnumType $type): void
	{
		self::assertSame(
			['type' => 'enum', 'cases' => $type->getCases()],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayType
	 */
	public function testArray(ArrayType $type): void
	{
		$type1 = ArrayType::forArray(new SimpleValueType('string'), new SimpleValueType('test'));

		self::assertSame(
			[
				'type' => 'array',
				'parameters' => [],
				'key' => null,
				'item' => null,
				'invalidPairs' => [],
			],
			$this->formatter->printType($type1),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeInvalid
	 */
	public function testArrayInvalid(ArrayType $type): void
	{
		self::assertSame(
			[
				'type' => 'array',
				'parameters' => [],
				'key' => null,
				'item' => [
					'type' => 'simple',
					'name' => 'test',
					'parameters' => [
						[
							'key' => 'parameter',
						],
					],
				],
				'invalidPairs' => [],
			],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeSimpleInvalid
	 */
	public function testArraySimpleInvalid(ArrayType $type): void
	{
		self::assertSame(
			[
				'type' => 'array',
				'parameters' => [],
				'key' => [
					'type' => 'simple',
					'name' => 'string',
					'parameters' => [],
				],
				'item' => [
					'type' => 'simple',
					'name' => 'test',
					'parameters' => [],
				],
				'invalidPairs' => [],
			],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeSimpleInvalidWithParameters
	 */
	public function testArraySimpleInvalidWithParameters(ArrayType $type): void
	{
		self::assertSame(
			[
				'type' => 'array',
				'parameters' => [
					[
						'key' => 'foo',
						'value' => 'bar',
					],
					[
						'key' => 'baz',
						'value' => 123,
					],
				],
				'key' => [
					'type' => 'simple',
					'name' => 'string',
					'parameters' => [],
				],
				'item' => [
					'type' => 'simple',
					'name' => 'test',
					'parameters' => [],
				],
				'invalidPairs' => [],
			],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeCompoundInvalid
	 */
	public function testArrayTypeCompoundInvalid(ArrayType $type): void
	{
		self::assertSame(
			[
				'type' => 'array',
				'parameters' => [],
				'key' => [
					'type' => 'compound',
					'operator' => '||',
					'subtypes' => [
						[
							'type' => 'simple',
							'name' => 'string',
							'parameters' => [],
						],
						[
							'type' => 'simple',
							'name' => 'int',
							'parameters' => [],
						],
					],
				],
				'item' => [
					'type' => 'array',
					'parameters' => [],
					'key' => [
						'type' => 'simple',
						'name' => 'string',
						'parameters' => [],
					],
					'item' => [
						'type' => 'simple',
						'name' => 'test',
						'parameters' => [],
					],
					'invalidPairs' => [],
				],
				'invalidPairs' => [],
			],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeSimpleInvalidWithInvalidParameters
	 */
	public function testArrayTypeSimpleInvalidWithInvalidParameters(ArrayType $type): void
	{
		self::assertSame(
			[
				'type' => 'array',
				'parameters' => [
					[
						'key' => 'first',
						'value' => 'value',
					],
					[
						'key' => 'second',
					],
				],
				'key' => null,
				'item' => null,
				'invalidPairs' => [],
			],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideArrayTypeInvalidPairs
	 */
	public function testArrayTypeInvalidPairs(ArrayType $type): void
	{
		self::assertSame(
			[
				'type' => 'array',
				'parameters' => [],
				'key' => null,
				'item' => null,
				'invalidPairs' => [
					'test' => [
						'key' => [
							'type' => 'simple',
							'name' => 'string',
							'parameters' => [],
						],
						'value' => null,
					],
					0 => [
						'key' => null,
						'value' => [
							'type' => 'simple',
							'name' => 'int',
							'parameters' => [
								[
									'key' => 'second',

								],
							],
						],
					],
					123 => [
						'key' => [
							'type' => 'simple',
							'name' => 'string',
							'parameters' => [],
						],
						'value' => [
							'type' => 'simple',
							'name' => 'int',
							'parameters' => [
								[
									'key' => 'first',
									'value' => 'value',
								],
							],
						],
					],
				],
			],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideListType
	 */
	public function testListType(ArrayType $type): void
	{
		self::assertSame(
			[
				'type' => 'list',
				'parameters' => [],
				'key' => null,
				'item' => null,
				'invalidPairs' => [],
			],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideListTypeInvalid
	 */
	public function testListTypeInvalid(ArrayType $type): void
	{
		self::assertSame(
			[
				'type' => 'list',
				'parameters' => [],
				'key' => null,
				'item' => [
					'type' => 'simple',
					'name' => 'string',
					'parameters' => [],
				],
				'invalidPairs' => [],
			],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideListTypeInvalidWithParameter
	 */
	public function testListTypeInvalidWithParameter(ArrayType $type): void
	{
		self::assertSame(
			[
				'type' => 'list',
				'parameters' => [
					[
						'key' => 'foo',
						'value' => 'bar',
					],
				],
				'key' => null,
				'item' => [
					'type' => 'simple',
					'name' => 'string',
					'parameters' => [],
				],
				'invalidPairs' => [],
			],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideListTypeInvalidWithInvalidParameter
	 */
	public function testListTypeInvalidWithInvalidParameter(ArrayType $type): void
	{
		self::assertSame(
			[
				'type' => 'list',
				'parameters' => [
					[
						'key' => 'foo',
						'value' => 'bar',
					],
				],
				'key' => null,
				'item' => null,
				'invalidPairs' => [],
			],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideListTypeWithInvalidValues
	 */
	public function testListTypeWithInvalidValues(ArrayType $type): void
	{
		self::assertSame(
			[
				'type' => 'list',
				'parameters' => [],
				'key' => null,
				'item' => null,
				'invalidPairs' => [
					0 => [
						'key' => null,
						'value' => [
							'type' => 'simple',
							'name' => 'string',
							'parameters' => [],
						],
					],
					1 => [
						'key' => null,
						'value' => [
							'type' => 'simple',
							'name' => 'string',
							'parameters' => [],
						],
					],
					'test' => [
						'key' => null,
						'value' => [
							'type' => 'simple',
							'name' => 'string',
							'parameters' => [],
						],
					],
				],
			],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideCompoundTypeOverwriteSubtype
	 */
	public function testCompoundTypeOverwriteSubtype(CompoundType $type): void
	{
		self::assertSame(
			['type' => 'compound', 'operator' => '||',
				'subtypes' => [
					[
						'type' => 'simple',
						'name' => 'string',
						'parameters' => [],
					],
					[
						'type' => 'simple',
						'name' => 'int',
						'parameters' => [],
					],

				]],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideCompoundTypeOverwriteSubtypeComplex
	 */
	public function testCompoundTypeOverwriteSubtypeComplex(CompoundType $type): void
	{
		self::assertSame(
			[
				'type' => 'compound',
				'operator' => '||',
				'subtypes' => [
					[
						'type' => 'compound',
						'operator' => '&&',
						'subtypes' => [
							[
								'type' => 'simple',
								'name' => 'int',
								'parameters' => [],
							],
							['type' => 'simple',
								'name' => 'float',
								'parameters' => [],
							],
						],
					],
					[
						'type' => 'compound',
						'operator' => '&&',
						'subtypes' => [
							[
								'type' => 'simple',
								'name' => 'foo',
								'parameters' => [],
							],
							[
								'type' => 'simple',
								'name' => 'bar',
								'parameters' => [],
							],
						],
					],
				],

			],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideMappedObjectType
	 */
	public function testMappedObjectType(MappedObjectType $type): void
	{
		self::assertSame(
			[
				'type' => 'shape',
				'fields' => [],
				'errors' => [],
			],
			$this->formatter->printType($type),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideMappedObjectTypeInvalid
	 */
	public function testMappedObjectTypeInvalid(MappedObjectType $type): void
	{
		$printedType = [
			'type' => 'shape',
			'fields' => [
				0 => [
					'type' => 'simple',
					'name' => 't',
					'parameters' => [],
				],
				'a' => [
					'type' => 'simple',
					'name' => 't',
					'parameters' => [],
				],
				'b' => [
					'type' => 'shape',
					'fields' => [
						'foo' => [
							'type' => 'simple',
							'name' => 't',
							'parameters' => [],
						],
						'bar' => [
							'type' => 'simple',
							'name' => 't',
							'parameters' => [],
						],
					],
					'errors' => [],
				],
			],
			'errors' => [
				[
					'type' => 'message',
					'message' => 'Whole structure is invalid',
				],
			],
		];
		self::assertSame(
			$printedType,
			$this->formatter->printType($type),
		);
		self::assertSame(
			['path' => [
				'to' => ['error' => $printedType],
			]],
			$this->formatter->printError(
				InvalidData::create($type, Value::none()),
				['path', 'to', 'error'],
			),
		);
		self::assertSame(
			$printedType,
			$this->formatter->printError(InvalidData::create($type, Value::none())),
		);
	}

	/**
	 * @dataProvider \Orisai\ObjectMapper\Tester\TypesTestProvider::provideMappedObjectTypeInvalidWithInvalidFields
	 */
	public function testMappedObjectTypeInvalidWithInvalidFields(MappedObjectType $type): void
	{
		$printedType = [
			'type' => 'shape',
			'fields' => [
				0 => [
					'type' => 'simple',
					'name' => 'overwritten',
					'parameters' => [],
				],
				'b' => [
					'type' => 'shape',
					'fields' => [
						'foo' => [
							'type' => 'simple',
							'name' => 'overwritten',
							'parameters' => [],
						],
					],
					'errors' => [],
				],
			],
			'errors' => [
				[
					'type' => 'message',
					'message' => 'Random error',
				],
			],
		];

		self::assertSame(
			$printedType,
			$this->formatter->printType($type),
		);
		self::assertSame(
			$printedType,
			$this->formatter->printError(InvalidData::create($type, Value::none())),
		);
	}

}
