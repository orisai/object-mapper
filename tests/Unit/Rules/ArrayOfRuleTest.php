<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Exception\WithTypeAndValue;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;
use Orisai\ObjectMapper\Rules\ArrayOfArgs;
use Orisai\ObjectMapper\Rules\ArrayOfRule;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Rules\StringArgs;
use Orisai\ObjectMapper\Rules\StringRule;
use Orisai\ObjectMapper\Types\GenericArrayType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Tests\Orisai\ObjectMapper\Doubles\AlwaysInvalidRule;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class ArrayOfRuleTest extends ProcessingTestCase
{

	private ArrayOfRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new ArrayOfRule();
		$this->ruleManager->addRule(new AlwaysInvalidRule());
	}

	public function testProcessValid(): void
	{
		$value = ['foo', 'bar', 'baz', 123];
		$defaults = ['lorem', 'ipsum'];

		$processed = $this->rule->processValue(
			$value,
			new ArrayOfArgs(
				new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
			),
			$this->fieldContext(DefaultValueMeta::fromValue($defaults)),
		);

		self::assertSame($value, $processed);
	}

	public function testProcessValidWithKeys(): void
	{
		$value = ['a' => 'foo', 'b' => 'bar', 'c' => 'baz', 'd' => 123];

		$processed = $this->rule->processValue(
			$value,
			new ArrayOfArgs(
				new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
				new RuleRuntimeMeta(StringRule::class, new StringArgs()),
			),
			$this->fieldContext(),
		);

		self::assertSame($value, $processed);
	}

	public function testProcessDefaultsExceedLimit(): void
	{
		$value = ['foo', 'bar', 'baz', 'key' => ['foo', 'bar'], 'key2' => ['foo', 'bar']];
		$defaults = [456, 789, 'lorem', 'ipsum', 'key' => ['baz'], 'key2' => 'baz'];

		$processed = $this->rule->processValue(
			$value,
			new ArrayOfArgs(
				new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
				null,
				null,
				5,
				true,
			),
			$this->fieldContext(DefaultValueMeta::fromValue($defaults)),
		);

		self::assertSame(
			[456, 789, 'lorem', 'ipsum', 'key' => ['baz', 'foo', 'bar'], 'key2' => ['foo', 'bar'], 'foo', 'bar', 'baz'],
			$processed,
		);
	}

	public function testProcessInvalid(): void
	{
		$exception = null;
		$value = null;

		try {
			$this->rule->processValue(
				$value,
				new ArrayOfArgs(
					new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
				),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(GenericArrayType::class, $type);

			self::assertTrue($type->isInvalid());
			self::assertSame($value, $exception->getValue()->get());
		}

		self::assertNotNull($exception);
	}

	public function testProcessInvalidParameterMinAndInvalidValuesAndKeys(): void
	{
		$exception = null;
		$value = ['foo' => 'bar', 'baz' => 123, 10 => 456, 11 => 'test'];

		try {
			$this->rule->processValue(
				$value,
				new ArrayOfArgs(
					new RuleRuntimeMeta(StringRule::class, new StringArgs()),
					new RuleRuntimeMeta(StringRule::class, new StringArgs()),
					10,
				),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(GenericArrayType::class, $type);

			self::assertFalse($type->isInvalid());
			self::assertSame($value, $exception->getValue()->get());
			self::assertTrue($type->getParameter(ArrayOfRule::MinItems)->isInvalid());

			self::assertTrue($type->hasInvalidPairs());
			$invalidPairs = $type->getInvalidPairs();
			self::assertCount(3, $invalidPairs);

			$pair = $invalidPairs['baz'];
			self::assertNull($pair->getKey());
			self::assertInstanceOf(WithTypeAndValue::class, $pair->getValue());

			$pair = $invalidPairs[10];
			self::assertInstanceOf(WithTypeAndValue::class, $pair->getKey());
			self::assertInstanceOf(WithTypeAndValue::class, $pair->getValue());

			$pair = $invalidPairs[11];
			self::assertInstanceOf(WithTypeAndValue::class, $pair->getKey());
			self::assertNull($pair->getValue());
		}

		self::assertNotNull($exception);
	}

	public function testProcessInvalidParameterMax(): void
	{
		$exception = null;
		$value = ['foo', 3 => 'bar', 'baz', 123, 456];

		try {
			$this->rule->processValue(
				$value,
				new ArrayOfArgs(
					new RuleRuntimeMeta(StringRule::class, new StringArgs()),
					null,
					null,
					2,
				),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(GenericArrayType::class, $type);

			self::assertFalse($type->isInvalid());
			self::assertFalse($type->hasParameter(ArrayOfRule::MinItems));
			self::assertTrue($type->getParameter(ArrayOfRule::MaxItems)->isInvalid());
			self::assertFalse($type->hasInvalidPairs());
			self::assertSame($value, $exception->getValue()->get());
		}

		self::assertNotNull($exception);
	}

	public function testProcessInvalidItemsOnly(): void
	{
		$exception = null;
		$value = ['foo', 123, 456];

		try {
			$this->rule->processValue(
				$value,
				new ArrayOfArgs(
					new RuleRuntimeMeta(StringRule::class, new StringArgs()),
				),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(GenericArrayType::class, $type);

			self::assertFalse($type->isInvalid());
			self::assertFalse($type->hasInvalidParameters());
			self::assertTrue($type->hasInvalidPairs());
			self::assertFalse($exception->getValue()->has());

			self::assertCount(2, $type->getInvalidPairs());
		}

		self::assertNotNull($exception);
	}

	public function testType(): void
	{
		$args = new ArrayOfArgs(
			new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
		);

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertInstanceOf(SimpleValueType::class, $type->getItemType());
		self::assertNull($type->getKeyType());
		self::assertCount(0, $type->getParameters());
	}

	public function testTypeWithArgs(): void
	{
		$args = new ArrayOfArgs(
			new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
			new RuleRuntimeMeta(StringRule::class, new StringArgs()),
			10,
			100,
		);

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertInstanceOf(SimpleValueType::class, $type->getItemType());
		self::assertInstanceOf(SimpleValueType::class, $type->getKeyType());

		self::assertCount(2, $type->getParameters());
		self::assertTrue($type->hasParameter(ArrayOfRule::MinItems));
		self::assertSame(10, $type->getParameter(ArrayOfRule::MinItems)->getValue());
		self::assertTrue($type->hasParameter(ArrayOfRule::MaxItems));
		self::assertSame(100, $type->getParameter(ArrayOfRule::MaxItems)->getValue());
	}

}
