<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Rules\UrlRule;
use Orisai\ObjectMapper\Types\SimpleValueType;
use stdClass;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class UrlRuleTest extends ProcessingTestCase
{

	private UrlRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new UrlRule();
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
		yield ['//www.example.com/path?querykey=queryvalue'];
		yield ['http://username:password@hostname:9090/path?arg=value#anchor'];
		yield ['ftp://username:password@hostname:21/path;type=loremipsum'];
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
				new EmptyArgs(),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(SimpleValueType::class, $type);

			self::assertSame('url', $type->getName());
			self::assertSame($value, $exception->getValue()->get());
		}

		self::assertNotNull($exception);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideInvalidValues(): Generator
	{
		yield [0];
		yield [0.12];
		yield [new stdClass()];
		yield [true];
		yield [null];
		yield ['http:///'];
	}

	public function testType(): void
	{
		$args = new EmptyArgs();

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('url', $type->getName());
		self::assertSame(
			[],
			$type->getParameters(),
		);
	}

	/**
	 * @dataProvider providePhpNode
	 */
	public function testPhpNode(EmptyArgs $args, string $input, string $output): void
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
			new EmptyArgs(),
			'string',
			'string',
		];
	}

}
