<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\BoolArgs;
use Orisai\ObjectMapper\Rules\BoolRule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use stdClass;
use Tests\Orisai\ObjectMapper\Toolkit\RuleTestCase;
use function assert;

final class BoolRuleTest extends RuleTestCase
{

	private BoolRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new BoolRule();
	}

	/**
	 * @dataProvider provideValidValues
	 * @param mixed $value
	 */
	public function testProcessValid($value): void
	{
		$processed = $this->rule->processValue(
			$value,
			BoolArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext())),
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
	 * @dataProvider provideBoolLikeValues
	 * @param mixed $value
	 */
	public function testProcessBoolLike($value, bool $expected): void
	{
		$processed = $this->rule->processValue(
			$value,
			BoolArgs::fromArray($this->rule->resolveArgs([
				BoolRule::CAST_BOOL_LIKE => true,
			], $this->ruleArgsContext())),
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
		yield ['false', false];
		yield ['FALSE', false];
		yield ['fAlSe', false];
		yield [0, false];
	}

	/**
	 * @dataProvider provideInvalidValues
	 * @param mixed $value
	 */
	public function testProcessInvalid($value): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				$value,
				BoolArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof SimpleValueType);

			self::assertSame('bool', $type->getName());
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
		$args = BoolArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext()));

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
		$args = BoolArgs::fromArray($this->rule->resolveArgs([
			BoolRule::CAST_BOOL_LIKE => true,
		], $this->ruleArgsContext()));

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

}
