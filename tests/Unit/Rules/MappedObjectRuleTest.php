<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Rules\MappedObjectArgs;
use Orisai\ObjectMapper\Rules\MappedObjectRule;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;
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

	/**
	 * @param array<mixed> $args
	 *
	 * @dataProvider provideResolveValid
	 */
	public function testResolveValid(array $args, MappedObjectArgs $expectedArgs): void
	{
		$resolvedArgs = $this->rule->resolveArgs($args, $this->argsFieldContext());
		self::assertEquals($expectedArgs, $resolvedArgs);
	}

	public static function provideResolveValid(): Generator
	{
		yield [
			[
				MappedObjectRule::ClassName => DefaultsVO::class,
			],
			new MappedObjectArgs(DefaultsVO::class),
		];

		yield [
			[
				MappedObjectRule::ClassName => NoDefaultsVO::class,
			],
			new MappedObjectArgs(NoDefaultsVO::class),
		];
	}

	public function testProcessValid(): void
	{
		$options = new Options();
		$options->setPrefillDefaultValues();

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

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame(DefaultsVO::class, $type->getClass());
		self::assertSame(
			['string', 'nullableString', 'arrayOfMixed'],
			array_keys($type->getFields()),
		);
	}

}
