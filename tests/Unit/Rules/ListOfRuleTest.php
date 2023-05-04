<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;
use Orisai\ObjectMapper\Rules\ListOfRule;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Rules\MultiValueArgs;
use Orisai\ObjectMapper\Rules\StringArgs;
use Orisai\ObjectMapper\Rules\StringRule;
use Orisai\ObjectMapper\Types\GenericArrayType;
use Orisai\ObjectMapper\Types\KeyValueErrorPair;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Tests\Orisai\ObjectMapper\Doubles\AlwaysInvalidRule;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class ListOfRuleTest extends ProcessingTestCase
{

	private ListOfRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new ListOfRule();
		$this->ruleManager->addRule(new AlwaysInvalidRule());
	}

	public function testProcessValid(): void
	{
		$value = ['foo', 'bar', 'baz', 123];
		$defaults = ['lorem', 'ipsum'];

		$processed = $this->rule->processValue(
			$value,
			new MultiValueArgs(
				new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
			),
			$this->fieldContext(DefaultValueMeta::fromValue($defaults)),
		);

		self::assertSame($value, $processed);
	}

	public function testProcessDefaultsExceedLimit(): void
	{
		$value = ['foo', 'bar', 'baz', 123];
		$defaults = [456, 789, 'lorem', 'ipsum'];

		$processed = $this->rule->processValue(
			$value,
			new MultiValueArgs(
				new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
				null,
				5,
				true,
			),
			$this->fieldContext(DefaultValueMeta::fromValue($defaults)),
		);

		self::assertSame([456, 789, 'lorem', 'ipsum', 'foo', 'bar', 'baz', 123], $processed);
	}

	public function testProcessInvalid(): void
	{
		$exception = null;
		$value = null;

		try {
			$this->rule->processValue(
				$value,
				new MultiValueArgs(
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
		$value = ['foo', 3 => 'bar', 'baz', 'key' => 123, 456];

		try {
			$this->rule->processValue(
				$value,
				new MultiValueArgs(
					new RuleRuntimeMeta(StringRule::class, new StringArgs()),
					10,
				),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(GenericArrayType::class, $type);

			self::assertFalse($type->isInvalid());
			self::assertTrue($type->getParameter(ListOfRule::MinItems)->isInvalid());
			self::assertSame($value, $exception->getValue()->get());

			self::assertTrue($type->hasInvalidPairs());
			$invalidItems = $type->getInvalidPairs();
			self::assertCount(3, $invalidItems);

			self::assertInstanceOf(KeyValueErrorPair::class, $invalidItems[3]);
			self::assertInstanceOf(SimpleValueType::class, $invalidItems[3]->getKey()->getType());
			self::assertNull($invalidItems[3]->getValue());

			self::assertInstanceOf(KeyValueErrorPair::class, $invalidItems['key']);
			self::assertInstanceOf(SimpleValueType::class, $invalidItems['key']->getKey()->getType());
			self::assertInstanceOf(SimpleValueType::class, $invalidItems['key']->getValue()->getType());

			self::assertInstanceOf(KeyValueErrorPair::class, $invalidItems[5]);
			self::assertNull($invalidItems[5]->getKey());
			self::assertInstanceOf(SimpleValueType::class, $invalidItems[5]->getValue()->getType());
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
				new MultiValueArgs(
					new RuleRuntimeMeta(StringRule::class, new StringArgs()),
					null,
					2,
				),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getType();
			self::assertInstanceOf(GenericArrayType::class, $type);

			self::assertFalse($type->isInvalid());
			self::assertFalse($type->hasParameter(ListOfRule::MinItems));
			self::assertTrue($type->getParameter(ListOfRule::MaxItems)->isInvalid());
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
				new MultiValueArgs(
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
		$args = new MultiValueArgs(
			new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
		);

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertInstanceOf(SimpleValueType::class, $type->getItemType());
		self::assertCount(0, $type->getParameters());
	}

	public function testTypeWithArgs(): void
	{
		$args = new MultiValueArgs(
			new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
			10,
			100,
		);

		$type = $this->rule->createType($args, $this->createTypeContext());

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertInstanceOf(SimpleValueType::class, $type->getItemType());

		self::assertCount(2, $type->getParameters());
		self::assertTrue($type->hasParameter(ListOfRule::MinItems));
		self::assertSame(10, $type->getParameter(ListOfRule::MinItems)->getValue());
		self::assertTrue($type->hasParameter(ListOfRule::MaxItems));
		self::assertSame(100, $type->getParameter(ListOfRule::MaxItems)->getValue());
	}

}
