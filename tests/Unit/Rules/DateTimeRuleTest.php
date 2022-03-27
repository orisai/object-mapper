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
use const PHP_VERSION_ID;

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
	public function testProcessValid($value, string $format): void
	{
		$processed = $this->rule->processValue(
			$value,
			new DateTimeArgs($format),
			$this->fieldContext(),
		);

		self::assertSame($value, $processed);

		$instantiated = $this->rule->processValue(
			$value,
			new DateTimeArgs($format),
			$this->fieldContext(null, null, true),
		);

		self::assertInstanceOf(DateTimeImmutable::class, $instantiated);

		$instantiatedType = $this->rule->processValue(
			$value,
			new DateTimeArgs($format, DateTime::class),
			$this->fieldContext(null, null, true),
		);

		self::assertInstanceOf(DateTime::class, $instantiatedType);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideValidValues(): Generator
	{
		yield ['now', DateTimeRule::FORMAT_ANY];
		yield ['yesterday', DateTimeRule::FORMAT_ANY];
		yield ['1879-03-14', DateTimeRule::FORMAT_ANY];
		yield ['2013-04-12T16:40:00-04:00', DateTimeRule::FORMAT_ANY];
		yield ['2013-04-12T16:40:00-04:00', DateTimeInterface::ATOM];
		yield ['1389312000', DateTimeRule::FORMAT_TIMESTAMP];
		yield [1_389_312_000, DateTimeRule::FORMAT_TIMESTAMP];
		yield ['1389312000', DateTimeRule::FORMAT_ANY];
		yield [1_389_312_000, DateTimeRule::FORMAT_ANY];
	}

	/**
	 * @param mixed              $value
	 * @param array<int, string> $invalidParameters
	 *
	 * @dataProvider provideInvalidValues
	 */
	public function testProcessInvalid(
		$value,
		string $format,
		array $invalidParameters = [],
		string $expectedType = 'datetime'
	): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				$value,
				new DateTimeArgs($format),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame($expectedType, $type->getName());
			self::assertSame($value, $exception->getValue()->get());

			$parameters = [];
			foreach ($type->getParameters() as $parameter) {
				if ($parameter->isInvalid()) {
					$parameterString = $parameter->getKey();
					if ($parameter->hasValue()) {
						$parameterString .= ": {$parameter->getValue()}";
					}

					$parameters[] = $parameterString;
				}
			}

			self::assertSame($parameters, $invalidParameters);
		}

		self::assertNotNull($exception);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideInvalidValues(): Generator
	{
		yield [null, DateTimeRule::FORMAT_ANY, []];
		yield [[], DateTimeRule::FORMAT_ANY, []];
		yield [true, DateTimeRule::FORMAT_ANY, []];
		yield [1.2, DateTimeRule::FORMAT_ANY, []];
		yield ['whatever', DateTimeRule::FORMAT_ANY, [
			'Failed to parse time string (whatever) at position 0 (w): The timezone could not be found in the database',
		]];

		yield ['whatever', DateTimeInterface::ATOM, [
			'format: Y-m-d\TH:i:sP',
			'A four digit year could not be found',
			'Data missing',
		]];

		yield ['whatever', DateTimeRule::FORMAT_TIMESTAMP, [
			PHP_VERSION_ID < 8_00_00 ? 'Unexpected data found.' : 'Found unexpected data',
		], 'timestamp'];

		yield ['2013-04-12T16:40:00-04:00', DateTimeInterface::COOKIE, [
			'format: l, d-M-Y H:i:s T',
			'A textual day could not be found',
			'Unexpected data found.',
			'The separation symbol could not be found',
		]];
	}

	public function testType(): void
	{
		$args = new DateTimeArgs();

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

	public function testTypeWithTimestamp(): void
	{
		$args = new DateTimeArgs(DateTimeRule::FORMAT_TIMESTAMP);

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
		$args = new DateTimeArgs(DateTimeInterface::COOKIE);

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('datetime', $type->getName());
		self::assertCount(1, $type->getParameters());
		self::assertTrue($type->hasParameter(DateTimeRule::FORMAT));
		self::assertSame(DateTimeInterface::COOKIE, $type->getParameter(DateTimeRule::FORMAT)->getValue());
	}

}
