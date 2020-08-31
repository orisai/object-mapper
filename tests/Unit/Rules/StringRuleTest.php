<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\StringArgs;
use Orisai\ObjectMapper\Rules\StringRule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use stdClass;
use Tests\Orisai\ObjectMapper\Toolkit\RuleTestCase;
use function assert;

final class StringRuleTest extends RuleTestCase
{

	private StringRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new StringRule();
	}

	/**
	 * @dataProvider provideValidValues
	 * @param mixed $value
	 */
	public function testProcessValid($value): void
	{
		$processed = $this->rule->processValue(
			$value,
			StringArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext())),
			$this->fieldContext(),
		);

		self::assertSame($value, $processed);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideValidValues(): Generator
	{
		yield ['foo'];
		yield [''];
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
				StringArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof SimpleValueType);

			self::assertSame('string', $type->getType());
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
		yield [true];
		yield [false];
		yield [new stdClass()];
		yield [123];
		yield [123.456];
	}

	public function testProcessInvalidParameters(): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				'',
				StringArgs::fromArray($this->rule->resolveArgs([
					StringRule::NOT_EMPTY => true,
					StringRule::MIN_LENGTH => 1,
					StringRule::MAX_LENGTH => 10,
					StringRule::PATTERN => '/[\s\S]/',
				], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof SimpleValueType);

			self::assertSame('string', $type->getType());
			self::assertTrue($type->hasInvalidParameters());
			self::assertTrue($type->getParameter(StringRule::NOT_EMPTY)->isInvalid());
			self::assertTrue($type->getParameter(StringRule::MIN_LENGTH)->isInvalid());
			self::assertTrue($type->getParameter(StringRule::PATTERN)->isInvalid());
			self::assertFalse($type->getParameter(StringRule::MAX_LENGTH)->isInvalid());
		}

		self::assertNotNull($exception);
	}

	public function testProcessAnotherInvalidParameters(): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				'I am longer than expected',
				StringArgs::fromArray($this->rule->resolveArgs([
					StringRule::NOT_EMPTY => true,
					StringRule::MIN_LENGTH => 1,
					StringRule::MAX_LENGTH => 10,
					StringRule::PATTERN => '/[\s\S]/',
				], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof SimpleValueType);

			self::assertSame('string', $type->getType());
			self::assertTrue($type->hasInvalidParameters());
			self::assertFalse($type->getParameter(StringRule::NOT_EMPTY)->isInvalid());
			self::assertFalse($type->getParameter(StringRule::MIN_LENGTH)->isInvalid());
			self::assertFalse($type->getParameter(StringRule::PATTERN)->isInvalid());
			self::assertTrue($type->getParameter(StringRule::MAX_LENGTH)->isInvalid());
		}

		self::assertNotNull($exception);
	}

	/**
	 * @dataProvider provideEmptyValues
	 */
	public function testProcessEmpty(string $value): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				$value,
				StringArgs::fromArray($this->rule->resolveArgs([
					StringRule::NOT_EMPTY => true,
				], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof SimpleValueType);

			self::assertSame('string', $type->getType());
			self::assertTrue($type->hasInvalidParameters());
			self::assertTrue($type->getParameter(StringRule::NOT_EMPTY)->isInvalid());
			self::assertFalse($type->hasParameter(StringRule::MIN_LENGTH));
			self::assertFalse($type->hasParameter(StringRule::PATTERN));
			self::assertFalse($type->hasParameter(StringRule::MAX_LENGTH));
		}

		self::assertNotNull($exception);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideEmptyValues(): Generator
	{
		yield [''];
		yield [' '];
		yield ['                     '];
	}

	public function testType(): void
	{
		$args = StringArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('string', $type->getType());
		self::assertCount(0, $type->getParameters());
	}

	public function testTypeWithArgs(): void
	{
		$args = StringArgs::fromArray($this->rule->resolveArgs([
			StringRule::NOT_EMPTY => true,
			StringRule::MIN_LENGTH => 1,
			StringRule::MAX_LENGTH => 10,
			StringRule::PATTERN => '/[\s\S]/',
		], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('string', $type->getType());

		self::assertCount(4, $type->getParameters());
		self::assertTrue($type->hasParameter(StringRule::NOT_EMPTY));
		self::assertFalse($type->getParameter(StringRule::NOT_EMPTY)->hasValue());
		self::assertTrue($type->hasParameter(StringRule::MIN_LENGTH));
		self::assertSame(1, $type->getParameter(StringRule::MIN_LENGTH)->getValue());
		self::assertTrue($type->hasParameter(StringRule::MAX_LENGTH));
		self::assertSame(10, $type->getParameter(StringRule::MAX_LENGTH)->getValue());
		self::assertTrue($type->hasParameter(StringRule::PATTERN));
		self::assertSame('/[\s\S]/', $type->getParameter(StringRule::PATTERN)->getValue());
	}

}
