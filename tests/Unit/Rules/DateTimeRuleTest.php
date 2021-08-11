<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Generator;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\DateTimeArgs;
use Orisai\ObjectMapper\Rules\DateTimeRule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Tests\Orisai\ObjectMapper\Toolkit\RuleTestCase;
use function assert;

final class DateTimeRuleTest extends RuleTestCase
{

	private DateTimeRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new DateTimeRule();
	}

	/**
	 * @param mixed $value
	 *
	 * @dataProvider provideValidValues
	 */
	public function testProcessValid($value, ?string $format = null): void
	{
		$processed = $this->rule->processValue(
			$value,
			DateTimeArgs::fromArray($this->rule->resolveArgs([
				DateTimeRule::FORMAT => $format,
			], $this->ruleArgsContext())),
			$this->fieldContext(),
		);

		self::assertSame($value, $processed);

		$instantiated = $this->rule->processValue(
			$value,
			DateTimeArgs::fromArray($this->rule->resolveArgs([
				DateTimeRule::FORMAT => $format,
			], $this->ruleArgsContext())),
			$this->fieldContext(null, null, true),
		);

		self::assertInstanceOf(DateTimeImmutable::class, $instantiated);

		$instantiatedType = $this->rule->processValue(
			$value,
			DateTimeArgs::fromArray($this->rule->resolveArgs([
				DateTimeRule::FORMAT => $format,
				DateTimeRule::TYPE => DateTime::class,
			], $this->ruleArgsContext())),
			$this->fieldContext(null, null, true),
		);

		self::assertInstanceOf(DateTime::class, $instantiatedType);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideValidValues(): Generator
	{
		yield ['now'];
		yield ['yesterday'];
		yield ['1879-03-14'];
		yield ['2013-04-12T16:40:00-04:00'];
		yield ['2013-04-12T16:40:00-04:00', DateTimeImmutable::ATOM];
		yield ['1389312000', DateTimeRule::FORMAT_TIMESTAMP];
		yield [1_389_312_000, DateTimeRule::FORMAT_TIMESTAMP];
		yield ['1389312000'];
		yield [1_389_312_000];
	}

	/**
	 * @param mixed $value
	 *
	 * @dataProvider provideInvalidValues
	 */
	public function testProcessInvalid($value, ?string $format = null, string $expectedType = 'datetime'): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				$value,
				DateTimeArgs::fromArray($this->rule->resolveArgs([
					DateTimeRule::FORMAT => $format,
				], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof SimpleValueType);

			self::assertSame($expectedType, $type->getName());
			self::assertSame($value, $exception->getInvalidValue());
		}

		self::assertNotNull($exception);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideInvalidValues(): Generator
	{
		yield ['whatever'];
		yield ['whatever', DateTimeImmutable::ATOM];
		yield ['whatever', DateTimeRule::FORMAT_TIMESTAMP, 'timestamp'];
		yield ['2013-04-12T16:40:00-04:00', DateTimeImmutable::COOKIE];
	}

	public function testType(): void
	{
		$args = DateTimeArgs::fromArray($this->rule->resolveArgs([], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('datetime', $type->getName());
		self::assertCount(0, $type->getParameters());
	}

	public function testTypeWithTimestamp(): void
	{
		$args = DateTimeArgs::fromArray($this->rule->resolveArgs([
			DateTimeRule::FORMAT => DateTimeRule::FORMAT_TIMESTAMP,
		], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('timestamp', $type->getName());
		self::assertCount(0, $type->getParameters());
	}

	public function testTypeWithArgs(): void
	{
		$args = DateTimeArgs::fromArray($this->rule->resolveArgs([
			DateTimeRule::FORMAT => DateTimeInterface::ATOM,
		], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('datetime', $type->getName());
		self::assertCount(1, $type->getParameters());
		self::assertTrue($type->hasParameter(DateTimeRule::FORMAT));
		self::assertSame(DateTimeInterface::ATOM, $type->getParameter(DateTimeRule::FORMAT)->getValue());
	}

}
