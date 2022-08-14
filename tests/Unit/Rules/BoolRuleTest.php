<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\BoolArgs;
use Orisai\ObjectMapper\Rules\BoolRule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use stdClass;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class BoolRuleTest extends ProcessingTestCase
{

	private BoolRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new BoolRule();
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
			new BoolArgs(),
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
	}

	/**
	 * @param mixed $value
	 *
	 * @dataProvider provideBoolLikeValues
	 */
	public function testProcessBoolLike($value, bool $expected): void
	{
		$processed = $this->rule->processValue(
			$value,
			new BoolArgs(true),
			$this->fieldContext(),
		);

		self::assertSame($expected, $processed);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideBoolLikeValues(): Generator
	{
		yield ['true', true];
		yield ['TRUE', true];
		yield ['tRuE', true];
		yield [1, true];
		yield ['1', true];
		yield ['false', false];
		yield ['FALSE', false];
		yield ['fAlSe', false];
		yield [0, false];
		yield ['0', false];
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
				new BoolArgs(),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame('bool', $type->getName());
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
		yield [['foo', 123, 123.456, true, false]];
		yield [''];
		yield [0];
		yield [0.012];
		yield ['0 foo'];
		yield [new stdClass()];
		yield ['true'];
		yield ['false'];
		yield [123];
		yield [123.456];
	}

	public function testType(): void
	{
		$args = new BoolArgs();

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('bool', $type->getName());
		self::assertCount(0, $type->getParameters());
	}

	public function testTypeWithArgs(): void
	{
		$args = new BoolArgs(true);

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('bool', $type->getName());

		self::assertCount(1, $type->getParameters());
		self::assertTrue($type->hasParameter('acceptsBoolLike'));
		self::assertFalse($type->getParameter('acceptsBoolLike')->hasValue());
	}

	/**
	 * @dataProvider providePhpNode
	 */
	public function testPhpNode(BoolArgs $args, string $input, string $output): void
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
			new BoolArgs(),
			'bool',
			'bool',
		];

		yield [
			new BoolArgs(true),
			"(bool|'true'|'false'|1|0)",
			'bool',
		];
	}

}
