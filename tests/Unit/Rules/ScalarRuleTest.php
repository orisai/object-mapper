<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\ScalarRule;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use stdClass;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class ScalarRuleTest extends ProcessingTestCase
{

	private ScalarRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new ScalarRule();
	}

	/**
	 * @param mixed $value
	 *
	 * @dataProvider provideValidValues
	 */
	public function testProcessValid($value): void
	{
		$processed = $this->rule->processValue(
			$value,
			new EmptyArgs(),
			$this->fieldContext(),
		);

		self::assertSame($value, $processed);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideValidValues(): Generator
	{
		yield [true];
		yield [false];
		yield ['foo'];
		yield [123];
		yield [123.456];
	}

	/**
	 * @param mixed $value
	 *
	 * @dataProvider provideInvalidValues
	 */
	public function testProcessInvalid($value): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				$value,
				new EmptyArgs(),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(CompoundType::class, $type);

			$subtypes = $type->getSubtypes();
			self::assertCount(4, $subtypes);
			foreach ($subtypes as $key => $subtype) {
				self::assertTrue($type->isSubtypeInvalid($key));
			}

			$invalidSubtypes = $type->getInvalidSubtypes();
			self::assertCount(4, $invalidSubtypes);
			foreach ($invalidSubtypes as $key => $invalidSubtype) {
				self::assertSame($invalidSubtype->getType(), $subtypes[$key]);
				self::assertFalse($invalidSubtype->getValue()->has());
			}

			self::assertSame($value, $exception->getValue()->get());
		}

		self::assertNotNull($exception);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideInvalidValues(): Generator
	{
		yield [[]];
		yield [['foo', 'bar']];
		yield [new stdClass()];
		yield [null];
	}

	public function testType(): void
	{
		$args = new EmptyArgs();

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		$subtypes = $type->getSubtypes();
		self::assertCount(4, $subtypes);

		self::assertInstanceOf(SimpleValueType::class, $subtypes[0]);
		self::assertSame('int', $subtypes[0]->getName());

		self::assertInstanceOf(SimpleValueType::class, $subtypes[1]);
		self::assertSame('float', $subtypes[1]->getName());

		self::assertInstanceOf(SimpleValueType::class, $subtypes[2]);
		self::assertSame('string', $subtypes[2]->getName());

		self::assertInstanceOf(SimpleValueType::class, $subtypes[3]);
		self::assertSame('bool', $subtypes[3]->getName());
	}

	/**
	 * @dataProvider providePhpNode
	 */
	public function testPhpNode(EmptyArgs $args, string $input, string $output): void
	{
		self::assertSame(
			$input,
			(string) $this->rule->getExpectedInputType($args, $this->fieldContext()),
		);

		self::assertSame(
			$output,
			(string) $this->rule->getReturnType($args, $this->fieldContext()),
		);
	}

	public function providePhpNode(): Generator
	{
		yield [
			new EmptyArgs(),
			'(int|float|string|bool)',
			'(int|float|string|bool)',
		];
	}

}
