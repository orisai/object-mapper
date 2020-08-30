<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\NullArgs;
use Orisai\ObjectMapper\Rules\NullRule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use stdClass;
use Tests\Orisai\ObjectMapper\Toolkit\RuleTestCase;
use function assert;

final class NullRuleTest extends RuleTestCase
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
			NullArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext())),
			$this->fieldContext(),
		);

		self::assertNull($processed);
	}

	/**
	 * @dataProvider provideNullLike
	 */
	public function testProcessNullLike(string $value): void
	{
		$processed = $this->rule->processValue(
			$value,
			NullArgs::fromArray($this->rule->resolveArgs([
				NullRule::CAST_EMPTY_STRING => true,
			], $this->ruleArgsContext())),
			$this->fieldContext(),
		);

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
	 * @dataProvider provideInvalidValues
	 * @param mixed $value
	 */
	public function testProcessInvalid($value): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				$value,
				NullArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof SimpleValueType);

			self::assertSame('null', $type->getType());
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
		$args = NullArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('null', $type->getType());
		self::assertCount(0, $type->getParameters());
	}

	public function testTypeWithArgs(): void
	{
		$args = NullArgs::fromArray($this->rule->resolveArgs([
			NullRule::CAST_EMPTY_STRING => true,
		], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('null', $type->getType());

		self::assertCount(1, $type->getParameters());
		self::assertTrue($type->hasParameter('acceptsEmptyString'));
		self::assertFalse($type->getParameter('acceptsEmptyString')->hasValue());
	}

}
