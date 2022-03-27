<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Rules\StructureArgs;
use Orisai\ObjectMapper\Rules\StructureRule;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use Tests\Orisai\ObjectMapper\Toolkit\RuleTestCase;
use function array_keys;

final class StructureRuleTest extends RuleTestCase
{

	private StructureRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new StructureRule();
	}

	public function testProcessValid(): void
	{
		$options = new Options();
		$options->setPreFillDefaultValues();

		$processed = $this->rule->processValue(
			[],
			new StructureArgs(DefaultsVO::class),
			$this->fieldContext(null, $options),
		);

		self::assertNotEmpty($processed);
	}

	public function testProcessValidInitialization(): void
	{
		$processed = $this->rule->processValue(
			[],
			new StructureArgs(DefaultsVO::class),
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
				new StructureArgs(DefaultsVO::class),
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
		$args = new StructureArgs(DefaultsVO::class);

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

}
