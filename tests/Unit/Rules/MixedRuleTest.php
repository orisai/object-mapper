<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Rules\MixedRule;
use stdClass;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class MixedRuleTest extends ProcessingTestCase
{

	private MixedRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new MixedRule();
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
		$args = new EmptyArgs();

		$type = $this->rule->createType($args, $this->createTypeContext());

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
