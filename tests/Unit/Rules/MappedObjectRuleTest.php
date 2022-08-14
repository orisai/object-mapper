<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Rules\MappedObjectArgs;
use Orisai\ObjectMapper\Rules\MappedObjectRule;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use Tests\Orisai\ObjectMapper\Doubles\EmptyVO;
use Tests\Orisai\ObjectMapper\Doubles\FieldNamesVO;
use Tests\Orisai\ObjectMapper\Doubles\IODoesNotMatchVO;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;
use function array_keys;

final class MappedObjectRuleTest extends ProcessingTestCase
{

	private MappedObjectRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new MappedObjectRule();
	}

	public function testProcessValid(): void
	{
		$options = new Options();
		$options->setPreFillDefaultValues();

		$processed = $this->rule->processValue(
			[],
			new MappedObjectArgs(DefaultsVO::class),
			$this->fieldContext(null, $options),
		);

		self::assertNotEmpty($processed);
	}

	public function testProcessValidInitialization(): void
	{
		$processed = $this->rule->processValue(
			[],
			new MappedObjectArgs(DefaultsVO::class),
			$this->fieldContext(null, null, true),
		);

		self::assertInstanceOf(DefaultsVO::class, $processed);
	}

	public function testProcessInvalid(): void
	{
		$exception = null;
		$value = null;

		try {
			$this->rule->processValue(
				$value,
				new MappedObjectArgs(DefaultsVO::class),
				$this->fieldContext(),
			);
		} catch (InvalidData $exception) {
			$type = $exception->getType();

			self::assertTrue($type->isInvalid());
			self::assertSame($value, $exception->getValue()->get());
		}

		self::assertNotNull($exception);
	}

	public function testType(): void
	{
		$args = new MappedObjectArgs(DefaultsVO::class);

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame(DefaultsVO::class, $type->getClass());
		self::assertSame(
			['string', 'nullableString', 'untypedNullableString', 'untypedNull', 'arrayOfMixed'],
			array_keys($type->getFields()),
		);
	}

	/**
	 * @dataProvider providePhpNode
	 */
	public function testPhpNode(MappedObjectArgs $args, string $input, string $inputShape, string $output): void
	{
		$inputNode = $this->rule->getExpectedInputType($args, $this->fieldContext());
		self::assertSame($input, (string) $inputNode);
		self::assertSame(
			$inputShape,
			$inputNode->getArrayShape(),
		);

		$outputNode = $this->rule->getReturnType($args, $this->fieldContext());
		self::assertSame($output, (string) $outputNode);
	}

	public function providePhpNode(): Generator
	{
		yield [
			new MappedObjectArgs(EmptyVO::class),
			EmptyVO::class,
			'array{}',
			EmptyVO::class,
		];

		yield [
			new MappedObjectArgs(FieldNamesVO::class),
			FieldNamesVO::class,
			'array{original: string, field: string, 123: string, swap2: string, swap1: string}',
			FieldNamesVO::class,
		];

		yield [
			new MappedObjectArgs(IODoesNotMatchVO::class),
			IODoesNotMatchVO::class,
			"array{bool: (bool|'true'|'false'|1|0), dateTime: string}",
			IODoesNotMatchVO::class,
		];
	}

}
