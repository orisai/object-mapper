<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\NullArgs;
use Orisai\ObjectMapper\Rules\NullRule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use stdClass;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class NullRuleTest extends ProcessingTestCase
{

	private NullRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new NullRule();
	}

	public function testProcessValid(): void
	{
		$processed = $this->rule->processValue(
			null,
			new NullArgs(false),
			$this->fieldContext(),
		);

		/** @phpstan-ignore-next-line  */
		self::assertNull($processed);
	}

	/**
	 * @dataProvider provideNullLike
	 */
	public function testProcessNullLike(string $value): void
	{
		$processed = $this->rule->processValue(
			$value,
			new NullArgs(true),
			$this->fieldContext(),
		);

		/** @phpstan-ignore-next-line  */
		self::assertNull($processed);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideNullLike(): Generator
	{
		yield [''];
		yield [' '];
		yield ['                     '];
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
				new NullArgs(false),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame('null', $type->getName());
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
		yield [true];
		yield [false];
		yield [new stdClass()];
		yield ['foo'];
		yield [123];
		yield [123.456];
	}

	public function testType(): void
	{
		$args = $this->ruleArgs(NullRule::class);

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('null', $type->getName());
		self::assertCount(0, $type->getParameters());
	}

	public function testTypeWithArgs(): void
	{
		$args = new NullArgs(true);

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('null', $type->getName());

		self::assertCount(1, $type->getParameters());
		self::assertTrue($type->hasParameter('acceptsEmptyString'));
		self::assertFalse($type->getParameter('acceptsEmptyString')->hasValue());
	}

}
