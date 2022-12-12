<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\StringArgs;
use Orisai\ObjectMapper\Rules\StringRule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use stdClass;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class StringRuleTest extends ProcessingTestCase
{

	private StringRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new StringRule();
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
			new StringArgs(),
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
				new StringArgs(),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame('string', $type->getName());
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
		yield [true];
		yield [false];
		yield [new stdClass()];
		yield [123];
		yield [123.456];
	}

	public function testProcessInvalidParameters(): void
	{
		$exception = null;
		$value = '';

		try {
			$this->rule->processValue(
				$value,
				new StringArgs('/[\s\S]/', true, 1, 10),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame('string', $type->getName());
			self::assertTrue($type->hasInvalidParameters());
			self::assertTrue($type->getParameter(StringRule::NotEmpty)->isInvalid());
			self::assertTrue($type->getParameter(StringRule::MinLength)->isInvalid());
			self::assertTrue($type->getParameter(StringRule::Pattern)->isInvalid());
			self::assertFalse($type->getParameter(StringRule::MaxLength)->isInvalid());
			self::assertSame($value, $exception->getValue()->get());
		}

		self::assertNotNull($exception);
	}

	public function testProcessAnotherInvalidParameters(): void
	{
		$exception = null;
		$value = 'I am longer than expected';

		try {
			$this->rule->processValue(
				$value,
				new StringArgs('/[\s\S]/', true, 1, 10),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame('string', $type->getName());
			self::assertTrue($type->hasInvalidParameters());
			self::assertFalse($type->getParameter(StringRule::NotEmpty)->isInvalid());
			self::assertFalse($type->getParameter(StringRule::MinLength)->isInvalid());
			self::assertFalse($type->getParameter(StringRule::Pattern)->isInvalid());
			self::assertTrue($type->getParameter(StringRule::MaxLength)->isInvalid());
			self::assertSame($value, $exception->getValue()->get());
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
				new StringArgs(null, true),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame('string', $type->getName());
			self::assertTrue($type->hasInvalidParameters());
			self::assertTrue($type->getParameter(StringRule::NotEmpty)->isInvalid());
			self::assertFalse($type->hasParameter(StringRule::MinLength));
			self::assertFalse($type->hasParameter(StringRule::Pattern));
			self::assertFalse($type->hasParameter(StringRule::MaxLength));
			self::assertSame($value, $exception->getValue()->get());
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
		$args = new StringArgs();

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('string', $type->getName());
		self::assertCount(0, $type->getParameters());
	}

	public function testTypeWithArgs(): void
	{
		$args = new StringArgs('/[\s\S]/', true, 1, 10);

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('string', $type->getName());

		self::assertCount(4, $type->getParameters());
		self::assertTrue($type->hasParameter(StringRule::NotEmpty));
		self::assertFalse($type->getParameter(StringRule::NotEmpty)->hasValue());
		self::assertTrue($type->hasParameter(StringRule::MinLength));
		self::assertSame(1, $type->getParameter(StringRule::MinLength)->getValue());
		self::assertTrue($type->hasParameter(StringRule::MaxLength));
		self::assertSame(10, $type->getParameter(StringRule::MaxLength)->getValue());
		self::assertTrue($type->hasParameter(StringRule::Pattern));
		self::assertSame('/[\s\S]/', $type->getParameter(StringRule::Pattern)->getValue());
	}

}
