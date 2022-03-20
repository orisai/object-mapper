<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\ScalarRule;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\NoValue;
use Orisai\ObjectMapper\Types\SimpleValueType;
use stdClass;
use Tests\Orisai\ObjectMapper\Toolkit\RuleTestCase;

final class ScalarRuleTest extends RuleTestCase
{

	private ScalarRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new ScalarRule();
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
			$this->rule->resolveArgs([], $this->ruleArgsContext()),
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
		yield ['foo'];
		yield [123];
		yield [123.456];
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
				$this->rule->resolveArgs([], $this->ruleArgsContext()),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			self::assertInstanceOf(CompoundType::class, $type);

			$subtypes = $type->getSubtypes();
			self::assertCount(4, $subtypes);
			foreach ($subtypes as $key => $subtype) {
				self::assertTrue($type->isSubtypeInvalid($key));
			}

			$invalidSubtypes = $type->getInvalidSubtypes();
			self::assertCount(4, $invalidSubtypes);
			foreach ($invalidSubtypes as $key => $invalidSubtype) {
				self::assertSame($invalidSubtype->getInvalidType(), $subtypes[$key]);
				self::assertInstanceOf(NoValue::class, $invalidSubtype->getInvalidValue());
			}

			self::assertSame($value, $exception->getInvalidValue());
		}

		self::assertNotNull($exception);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideInvalidValues(): Generator
	{
		yield [[]];
		yield [['foo', 'bar']];
		yield [new stdClass()];
		yield [null];
	}

	public function testType(): void
	{
		$args = $this->rule->resolveArgs([], $this->ruleArgsContext());

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		$subtypes = $type->getSubtypes();
		self::assertCount(4, $subtypes);

		self::assertInstanceOf(SimpleValueType::class, $subtypes[0]);
		self::assertSame('int', $subtypes[0]->getName());

		self::assertInstanceOf(SimpleValueType::class, $subtypes[1]);
		self::assertSame('float', $subtypes[1]->getName());

		self::assertInstanceOf(SimpleValueType::class, $subtypes[2]);
		self::assertSame('string', $subtypes[2]->getName());

		self::assertInstanceOf(SimpleValueType::class, $subtypes[3]);
		self::assertSame('bool', $subtypes[3]->getName());
	}

}
