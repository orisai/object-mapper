<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\InstanceArgs;
use Orisai\ObjectMapper\Rules\InstanceRule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use stdClass;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class InstanceRuleTest extends ProcessingTestCase
{

	private InstanceRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new InstanceRule();
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
			new InstanceArgs(stdClass::class),
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
				new InstanceArgs(stdClass::class),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame(stdClass::class, $type->getName());
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
		$args = new InstanceArgs(stdClass::class);

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame(stdClass::class, $type->getName());
		self::assertSame(
			[],
			$type->getParameters(),
		);
	}

	/**
	 * @dataProvider providePhpNode
	 */
	public function testPhpNode(InstanceArgs $args, string $input, string $output): void
	{
		self::assertSame(
			$input,
			(string) $this->rule->getExpectedInputType($args, $this->fieldContext()),
		);

		self::assertSame(
			$output,
			(string) $this->rule->getReturnType($args, $this->fieldContext()),
		);
	}

	public function providePhpNode(): Generator
	{
		yield [
			new InstanceArgs(stdClass::class),
			stdClass::class,
			stdClass::class,
		];
	}

}
