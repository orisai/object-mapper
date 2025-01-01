<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Generator;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\Rules\AnyOfRule;
use Orisai\ObjectMapper\Rules\CompoundArgs;
use Orisai\ObjectMapper\Rules\IntArgs;
use Orisai\ObjectMapper\Rules\IntRule;
use Orisai\ObjectMapper\Rules\MappedObjectArgs;
use Orisai\ObjectMapper\Rules\MappedObjectRule;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Rules\NullArgs;
use Orisai\ObjectMapper\Rules\NullRule;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\CompoundTypeOperator;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use Tests\Orisai\ObjectMapper\Doubles\Rules\AlwaysInvalidRule;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class AnyOfRuleTest extends ProcessingTestCase
{

	private AnyOfRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new AnyOfRule();
		$this->ruleManager->addRule(new AlwaysInvalidRule());
	}

	/**
	 * @param array<mixed> $args
	 *
	 * @dataProvider provideResolveValid
	 */
	public function testResolveValid(array $args, CompoundArgs $expectedArgs): void
	{
		$resolvedArgs = $this->rule->resolveArgs($args, $this->argsFieldContext());
		self::assertEquals($expectedArgs, $resolvedArgs);
	}

	public static function provideResolveValid(): Generator
	{
		yield [
			[
				AnyOfRule::Rules => [
					new RuleCompileMeta(MixedRule::class),
					new RuleCompileMeta(MixedRule::class),
				],
			],
			new CompoundArgs([
				new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
				new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
			]),
		];

		yield [
			[
				AnyOfRule::Rules => [
					new RuleCompileMeta(IntRule::class),
					new RuleCompileMeta(NullRule::class),
				],
			],
			new CompoundArgs([
				new RuleRuntimeMeta(IntRule::class, new IntArgs(0, 0, false, false)),
				new RuleRuntimeMeta(NullRule::class, new NullArgs(false)),
			]),
		];
	}

	public function testProcessValid(): void
	{
		$processed = $this->rule->processValue(
			'value',
			new CompoundArgs([
				new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
				new RuleRuntimeMeta(AlwaysInvalidRule::class, new EmptyArgs()),
			]),
			$this->fieldContext(),
		);

		self::assertSame('value', $processed);
	}

	public function testProcessInvalid(): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				'value',
				new CompoundArgs([
					new RuleRuntimeMeta(AlwaysInvalidRule::class, new EmptyArgs()),
					new RuleRuntimeMeta(AlwaysInvalidRule::class, new EmptyArgs()),
					new RuleRuntimeMeta(AlwaysInvalidRule::class, new EmptyArgs()),
				]),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(CompoundType::class, $type);

			self::assertSame(CompoundTypeOperator::or(), $type->getOperator());

			$subtypes = $type->getSubtypes();
			self::assertCount(3, $subtypes);

			self::assertTrue($type->isSubtypeInvalid(0));
			self::assertFalse($type->isSubtypeSkipped(0));

			self::assertTrue($type->isSubtypeInvalid(1));
			self::assertFalse($type->isSubtypeSkipped(1));

			self::assertTrue($type->isSubtypeInvalid(2));
			self::assertFalse($type->isSubtypeSkipped(2));

			self::assertFalse($exception->getValue()->has());
		}

		self::assertNotNull($exception);
	}

	public function testHandleValidationException(): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				null,
				new CompoundArgs([
					new RuleRuntimeMeta(AlwaysInvalidRule::class, new EmptyArgs()),
					new RuleRuntimeMeta(MappedObjectRule::class, new MappedObjectArgs(DefaultsVO::class)),
				]),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(CompoundType::class, $type);

			self::assertSame(CompoundTypeOperator::or(), $type->getOperator());

			$subtypes = $type->getSubtypes();
			self::assertCount(2, $subtypes);

			self::assertTrue($type->isSubtypeInvalid(0));
			self::assertFalse($type->isSubtypeSkipped(0));

			self::assertTrue($type->isSubtypeInvalid(1));
			self::assertFalse($type->isSubtypeSkipped(1));

			self::assertFalse($exception->getValue()->has());
		}

		self::assertNotNull($exception);
	}

	public function testType(): void
	{
		$args = new CompoundArgs([
			new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
			new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
			new RuleRuntimeMeta(AlwaysInvalidRule::class, new EmptyArgs()),
		]);

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame(CompoundTypeOperator::or(), $type->getOperator());

		$subtypes = $type->getSubtypes();
		self::assertCount(3, $subtypes);
		self::assertInstanceOf(SimpleValueType::class, $subtypes[0]);
		self::assertInstanceOf(SimpleValueType::class, $subtypes[1]);
		self::assertInstanceOf(MessageType::class, $subtypes[2]);
	}

}
