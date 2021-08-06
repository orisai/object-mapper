<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\MetaSource;
use Orisai\ObjectMapper\Rules\AnyOfRule;
use Orisai\ObjectMapper\Rules\CompoundRuleArgs;
use Orisai\ObjectMapper\Rules\FloatRule;
use Orisai\ObjectMapper\Rules\IntRule;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Rules\StructureRule;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Tests\Orisai\ObjectMapper\Doubles\AlwaysInvalidRule;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use Tests\Orisai\ObjectMapper\Toolkit\RuleTestCase;
use function assert;
use function sprintf;

final class AnyOfRuleTest extends RuleTestCase
{

	private AnyOfRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new AnyOfRule();
		$this->ruleManager->addRule(AlwaysInvalidRule::class, new AlwaysInvalidRule());
	}

	public function testProcessValid(): void
	{
		$processed = $this->rule->processValue(
			'value',
			CompoundRuleArgs::fromArray(
				$this->rule->resolveArgs(
					[
						AnyOfRule::RULES => [
							[MetaSource::OPTION_TYPE => MixedRule::class],
							[MetaSource::OPTION_TYPE => AlwaysInvalidRule::class],
						],
					],
					$this->ruleArgsContext(),
				),
			),
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
				CompoundRuleArgs::fromArray(
					$this->rule->resolveArgs(
						[
							AnyOfRule::RULES => [
								[MetaSource::OPTION_TYPE => AlwaysInvalidRule::class],
								[MetaSource::OPTION_TYPE => AlwaysInvalidRule::class],
								[MetaSource::OPTION_TYPE => AlwaysInvalidRule::class],
							],
						],
						$this->ruleArgsContext(),
					),
				),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof CompoundType);

			self::assertSame('|', $type->getOperator());

			$subtypes = $type->getSubtypes();
			self::assertCount(3, $subtypes);

			self::assertTrue($type->isSubtypeInvalid(0));
			self::assertFalse($type->isSubtypeSkipped(0));

			self::assertTrue($type->isSubtypeInvalid(1));
			self::assertFalse($type->isSubtypeSkipped(1));

			self::assertTrue($type->isSubtypeInvalid(2));
			self::assertFalse($type->isSubtypeSkipped(2));
		}

		self::assertNotNull($exception);
	}

	public function testProcessNestedRuleValueMutation(): void
	{
		$exception = null;
		$value = '123';
		try {
			$this->rule->processValue(
				$value,
				CompoundRuleArgs::fromArray(
					$this->rule->resolveArgs(
						[
							AnyOfRule::RULES => [
								[
									MetaSource::OPTION_TYPE => IntRule::class,
								],
								[
									MetaSource::OPTION_TYPE => FloatRule::class,
								],
							],
						],
						$this->ruleArgsContext(),
					),
				),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			self::assertSame($value, $exception->getInvalidValue());
		}

		self::assertNotNull($exception);
	}

	public function testHandleValidationException(): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				null,
				CompoundRuleArgs::fromArray(
					$this->rule->resolveArgs(
						[
							AnyOfRule::RULES => [
								[MetaSource::OPTION_TYPE => AlwaysInvalidRule::class],
								[
									MetaSource::OPTION_TYPE => StructureRule::class,
									MetaSource::OPTION_ARGS => [StructureRule::TYPE => DefaultsVO::class],
								],
							],
						],
						$this->ruleArgsContext(),
					),
				),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof CompoundType);

			self::assertSame('|', $type->getOperator());

			$subtypes = $type->getSubtypes();
			self::assertCount(2, $subtypes);

			self::assertTrue($type->isSubtypeInvalid(0));
			self::assertFalse($type->isSubtypeSkipped(0));

			self::assertTrue($type->isSubtypeInvalid(1));
			self::assertFalse($type->isSubtypeSkipped(1));
		}

		self::assertNotNull($exception);
	}

	public function testType(): void
	{
		$args = CompoundRuleArgs::fromArray(
			$this->rule->resolveArgs(
				[
					AnyOfRule::RULES => [
						[MetaSource::OPTION_TYPE => MixedRule::class],
						[MetaSource::OPTION_TYPE => MixedRule::class],
						[MetaSource::OPTION_TYPE => AlwaysInvalidRule::class],
					],
				],
				$this->ruleArgsContext(),
			),
		);

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertSame('|', $type->getOperator());
		self::assertSame('or', $type->getOperator(true));

		$subtypes = $type->getSubtypes();
		self::assertCount(3, $subtypes);
		self::assertInstanceOf(SimpleValueType::class, $subtypes[0]);
		self::assertInstanceOf(SimpleValueType::class, $subtypes[1]);
		self::assertInstanceOf(MessageType::class, $subtypes[2]);
	}

	public function testInnerRuleResolved(): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			sprintf(
				'"%s" does not accept any arguments, "foo" given',
				MixedRule::class,
			),
		);

		$this->rule->resolveArgs(
			[
				AnyOfRule::RULES => [
					[MetaSource::OPTION_TYPE => MixedRule::class],
					[
						MetaSource::OPTION_TYPE => MixedRule::class,
						MetaSource::OPTION_ARGS => ['foo' => 'bar'],
					],
				],
			],
			$this->ruleArgsContext(),
		);
	}

}
