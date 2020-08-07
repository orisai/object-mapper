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
			self::assertTrue($type->isParameterInvalid(StringRule::NOT_EMPTY));
			self::assertTrue($type->isParameterInvalid(StringRule::MIN_LENGTH));
			self::assertTrue($type->isParameterInvalid(StringRule::PATTERN));
			self::assertFalse($type->isParameterInvalid(StringRule::MAX_LENGTH));
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
			self::assertFalse($type->isParameterInvalid(StringRule::NOT_EMPTY));
			self::assertFalse($type->isParameterInvalid(StringRule::MIN_LENGTH));
			self::assertFalse($type->isParameterInvalid(StringRule::PATTERN));
			self::assertTrue($type->isParameterInvalid(StringRule::MAX_LENGTH));
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
			self::assertTrue($type->isParameterInvalid(StringRule::NOT_EMPTY));
			self::assertFalse($type->isParameterInvalid(StringRule::MIN_LENGTH));
			self::assertFalse($type->isParameterInvalid(StringRule::PATTERN));
			self::assertFalse($type->isParameterInvalid(StringRule::MAX_LENGTH));
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
		self::assertSame(
			[
				'notEmpty' => false,
				'minLength' => null,
				'maxLength' => null,
				'pattern' => null,
			],
			$type->getParameters(),
		);
	}

}
