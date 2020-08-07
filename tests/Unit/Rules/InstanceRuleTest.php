<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\InstanceArgs;
use Orisai\ObjectMapper\Rules\InstanceRule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use stdClass;
use Tests\Orisai\ObjectMapper\Toolkit\RuleTestCase;
use function assert;

final class InstanceRuleTest extends RuleTestCase
{

	private InstanceRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new InstanceRule();
	}

	/**
	 * @dataProvider provideValidValues
	 * @param mixed $value
	 */
	public function testProcessValid($value): void
	{
		$processed = $this->rule->processValue(
			$value,
			InstanceArgs::fromArray($this->rule->resolveArgs([
				InstanceRule::TYPE => stdClass::class,
			], $this->ruleArgsContext())),
			$this->fieldContext(),
		);

		self::assertSame($value, $processed);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideValidValues(): Generator
	{
		yield [new stdClass()];
		yield [new class extends stdClass {

		}];
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
				InstanceArgs::fromArray($this->rule->resolveArgs([
					InstanceRule::TYPE => stdClass::class,
				], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof SimpleValueType);

			self::assertSame(stdClass::class, $type->getType());
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
		yield [true];
		yield [false];
		yield ['foo'];
		yield [123];
		yield [123.456];
		yield [new class {

		}];
	}

	public function testType(): void
	{
		$args = InstanceArgs::fromArray($this->rule->resolveArgs([
			InstanceRule::TYPE => stdClass::class,
		], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame(stdClass::class, $type->getType());
		self::assertSame(
			[],
			$type->getParameters(),
		);
	}

}
