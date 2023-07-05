<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\Rules\AllOfRule;
use Orisai\ObjectMapper\Rules\CompoundArgs;
use Orisai\ObjectMapper\Rules\MappedObjectArgs;
use Orisai\ObjectMapper\Rules\MappedObjectRule;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\CompoundTypeOperator;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use Tests\Orisai\ObjectMapper\Doubles\Rules\AlwaysInvalidRule;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;
use function sprintf;

final class AllOfRuleTest extends ProcessingTestCase
{

	private AllOfRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new AllOfRule();
		$this->ruleManager->addRule(new AlwaysInvalidRule());
	}

	public function testProcessValid(): void
	{
		$processed = $this->rule->processValue(
			'value',
			new CompoundArgs([
				new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
				new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
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
					new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
					new RuleRuntimeMeta(AlwaysInvalidRule::class, new EmptyArgs()),
					new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
				]),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(CompoundType::class, $type);

			self::assertSame(CompoundTypeOperator::and(), $type->getOperator());

			$subtypes = $type->getSubtypes();
			self::assertCount(3, $subtypes);

			self::assertFalse($type->isSubtypeInvalid(0));
			self::assertFalse($type->isSubtypeSkipped(0));

			self::assertTrue($type->isSubtypeInvalid(1));
			self::assertFalse($type->isSubtypeSkipped(1));
			self::assertFalse($type->getInvalidSubtypes()[1]->getValue()->has());

			self::assertFalse($type->isSubtypeInvalid(2));
			self::assertTrue($type->isSubtypeSkipped(2));

			self::assertTrue($exception->getValue()->has());
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
					new RuleRuntimeMeta(MappedObjectRule::class, new MappedObjectArgs(
						DefaultsVO::class,
					)),
					new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
				]),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(CompoundType::class, $type);

			self::assertSame(CompoundTypeOperator::and(), $type->getOperator());

			$subtypes = $type->getSubtypes();
			self::assertCount(2, $subtypes);

			self::assertTrue($type->isSubtypeInvalid(0));
			self::assertFalse($type->isSubtypeSkipped(0));
			self::assertFalse($type->getInvalidSubtypes()[0]->getValue()->has());

			self::assertFalse($type->isSubtypeInvalid(1));
			self::assertTrue($type->isSubtypeSkipped(1));

			self::assertTrue($exception->getValue()->has());
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

		self::assertSame(CompoundTypeOperator::and(), $type->getOperator());

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
				AllOfRule::Rules => [
					new RuleCompileMeta(MixedRule::class),
					new RuleCompileMeta(MixedRule::class, [
						'foo' => 'bar',
					]),
				],
			],
			$this->argsContext(),
		);
	}

}
