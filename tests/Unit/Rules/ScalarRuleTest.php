<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\EmptyArgs;
use Orisai\ObjectMapper\Rules\ScalarRule;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use stdClass;
use Tests\Orisai\ObjectMapper\Toolkit\RuleTestCase;
use function assert;

final class ScalarRuleTest extends RuleTestCase
{

	private ScalarRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new ScalarRule();
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
		yield [true];
		yield [false];
		yield ['foo'];
		yield [123];
		yield [123.456];
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
				EmptyArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof CompoundType);

			$subtypes = $type->getSubtypes();
			self::assertCount(4, $subtypes);
			foreach ($subtypes as $key => $subtype) {
				self::assertTrue($type->isSubtypeInvalid($key));
			}
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
		$args = EmptyArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		$subtypes = $type->getSubtypes();
		self::assertCount(4, $subtypes);

		self::assertInstanceOf(SimpleValueType::class, $subtypes[0]);
		self::assertSame('int', $subtypes[0]->getType());

		self::assertInstanceOf(SimpleValueType::class, $subtypes[1]);
		self::assertSame('float', $subtypes[1]->getType());

		self::assertInstanceOf(SimpleValueType::class, $subtypes[2]);
		self::assertSame('string', $subtypes[2]->getType());

		self::assertInstanceOf(SimpleValueType::class, $subtypes[3]);
		self::assertSame('bool', $subtypes[3]->getType());
	}

}
