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
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;
use const PHP_VERSION_ID;

final class DateTimeRuleTest extends ProcessingTestCase
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
			new DateTimeArgs(DateTimeImmutable::class, $format),
			$this->fieldContext(),
		);

		self::assertSame($value, $processed);

		$instantiated = $this->rule->processValue(
			$value,
			new DateTimeArgs(DateTimeImmutable::class, $format),
			$this->fieldContext(null, null, true),
		);

		self::assertInstanceOf(DateTimeImmutable::class, $instantiated);

		$instantiatedType = $this->rule->processValue(
			$value,
			new DateTimeArgs(DateTime::class, $format),
			$this->fieldContext(null, null, true),
		);

		self::assertInstanceOf(DateTime::class, $instantiatedType);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideValidValues(): Generator
	{
		yield ['now', DateTimeRule::FormatAny];
		yield ['yesterday', DateTimeRule::FormatAny];
		yield ['1879-03-14', DateTimeRule::FormatAny];
		yield ['2013-04-12T16:40:00-04:00', DateTimeRule::FormatAny];
		yield ['2013-04-12T16:40:00-04:00', DateTimeInterface::ATOM];
		yield ['2013-04-12T16:40:00-04:00', DateTimeRule::FormatIsoCompat];
		yield ['2013-04-12T16:40:00.000Z', DateTimeRule::FormatIsoCompat];
		yield ['1389312000', DateTimeRule::FormatTimestamp];
		yield [1_389_312_000, DateTimeRule::FormatTimestamp];
		yield ['1389312000', DateTimeRule::FormatAny];
		yield [1_389_312_000, DateTimeRule::FormatAny];
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
				new DateTimeArgs(DateTimeImmutable::class, $format),
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
		yield [null, DateTimeRule::FormatAny, []];
		yield [[], DateTimeRule::FormatAny, []];
		yield [true, DateTimeRule::FormatAny, []];
		yield [1.2, DateTimeRule::FormatAny, []];
		yield ['whatever', DateTimeRule::FormatAny, [
			'Failed to parse time string (whatever) at position 0 (w): The timezone could not be found in the database',
		]];

		yield ['whatever', DateTimeInterface::ATOM, [
			'format: Y-m-d\TH:i:sP',
			'A four digit year could not be found',
			PHP_VERSION_ID < 8_01_07 ? 'Data missing' : 'Not enough data available to satisfy format',
		]];

		yield ['whatever', DateTimeRule::FormatTimestamp, [
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

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('datetime', $type->getName());
		self::assertCount(1, $type->getParameters());
		self::assertTrue($type->hasParameter(DateTimeRule::Format));
		self::assertSame('Y-m-d\TH:i:sP | Y-m-d\TH:i:s.v\Z', $type->getParameter(DateTimeRule::Format)->getValue());
	}

	public function testTypeWithTimestamp(): void
	{
		$args = new DateTimeArgs(DateTimeImmutable::class, DateTimeRule::FormatTimestamp);

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('timestamp', $type->getName());
		self::assertCount(0, $type->getParameters());
	}

	public function testTypeWithArgs(): void
	{
		$args = new DateTimeArgs(DateTimeImmutable::class, DateTimeInterface::COOKIE);

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('datetime', $type->getName());
		self::assertCount(1, $type->getParameters());
		self::assertTrue($type->hasParameter(DateTimeRule::Format));
		self::assertSame(DateTimeInterface::COOKIE, $type->getParameter(DateTimeRule::Format)->getValue());
	}

}
