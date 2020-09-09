<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Rules\EmptyArgs;
use Orisai\ObjectMapper\Rules\MixedRule;
use stdClass;
use Tests\Orisai\ObjectMapper\Toolkit\RuleTestCase;

final class MixedRuleTest extends RuleTestCase
{

	private MixedRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new MixedRule();
	}

	/**
	 * @dataProvider provideValidValues
	 * @param mixed $value
	 */
	public function testProcessValid($value): void
	{
		$processed = $this->rule->processValue(
			$value,
			EmptyArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext())),
			$this->fieldContext(),
		);

		self::assertSame($value, $processed);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideValidValues(): Generator
	{
		yield [[]];
		yield [['foo', 123, 123.456, true, false]];
		yield [null];
		yield [true];
		yield [false];
		yield [new stdClass()];
		yield ['foo'];
		yield [123];
		yield [123.456];
	}

	public function testType(): void
	{
		$args = EmptyArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('mixed', $type->getName());
		self::assertSame(
			[],
			$type->getParameters(),
		);
	}

}
