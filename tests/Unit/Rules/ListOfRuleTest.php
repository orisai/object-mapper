<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\DefaultValueMeta;
use Orisai\ObjectMapper\Meta\MetaSource;
use Orisai\ObjectMapper\Rules\ListOfRule;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Rules\MultiValueArgs;
use Orisai\ObjectMapper\Rules\StringRule;
use Orisai\ObjectMapper\Types\ListType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Tests\Orisai\ObjectMapper\Doubles\AlwaysInvalidRule;
use Tests\Orisai\ObjectMapper\Toolkit\RuleTestCase;
use function assert;

final class ListOfRuleTest extends RuleTestCase
{

	private ListOfRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new ListOfRule();
		$this->ruleManager->addRule(AlwaysInvalidRule::class, new AlwaysInvalidRule());
	}

	public function testProcessValid(): void
	{
		$value = ['foo', 'bar', 'baz', 123];
		$defaults = ['lorem', 'ipsum'];

		$processed = $this->rule->processValue(
			$value,
			MultiValueArgs::fromArray($this->rule->resolveArgs([
				ListOfRule::ITEM_RULE => [MetaSource::OPTION_TYPE => MixedRule::class],
			], $this->ruleArgsContext())),
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
			MultiValueArgs::fromArray($this->rule->resolveArgs([
				ListOfRule::ITEM_RULE => [MetaSource::OPTION_TYPE => MixedRule::class],
				ListOfRule::MAX_ITEMS => 5,
				ListOfRule::MERGE_DEFAULTS => true,
			], $this->ruleArgsContext())),
			$this->fieldContext(DefaultValueMeta::fromValue($defaults)),
		);

		self::assertSame([456, 789, 'lorem', 'ipsum', 'foo', 'bar', 'baz', 123], $processed);
	}

	public function testProcessInvalid(): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				null,
				MultiValueArgs::fromArray($this->rule->resolveArgs([
					ListOfRule::ITEM_RULE => [MetaSource::OPTION_TYPE => MixedRule::class],
				], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof ListType);

			self::assertTrue($type->isInvalid());
		}

		self::assertNotNull($exception);
	}

	public function testProcessInvalidParameterMinAndInvalidValuesAndKeys(): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				['foo', 3 => 'bar', 'baz', 123, 456],
				MultiValueArgs::fromArray($this->rule->resolveArgs([
					ListOfRule::ITEM_RULE => [MetaSource::OPTION_TYPE => StringRule::class],
					ListOfRule::MIN_ITEMS => 10,
				], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof ListType);

			self::assertFalse($type->isInvalid());
			self::assertTrue($type->areKeysInvalid());
			self::assertTrue($type->getParameter(ListOfRule::MIN_ITEMS)->isInvalid());
			self::assertTrue($type->hasInvalidItems());
			$invalidItems = $type->getInvalidItems();
			self::assertCount(2, $invalidItems);
		}

		self::assertNotNull($exception);
	}

	public function testProcessInvalidParameterMax(): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				['foo', 3 => 'bar', 'baz', 123, 456],
				MultiValueArgs::fromArray($this->rule->resolveArgs([
					ListOfRule::ITEM_RULE => [MetaSource::OPTION_TYPE => StringRule::class],
					ListOfRule::MAX_ITEMS => 2,
				], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof ListType);

			self::assertFalse($type->isInvalid());
			self::assertFalse($type->areKeysInvalid());
			self::assertFalse($type->hasParameter(ListOfRule::MIN_ITEMS));
			self::assertTrue($type->getParameter(ListOfRule::MAX_ITEMS)->isInvalid());
			self::assertFalse($type->hasInvalidItems());
		}

		self::assertNotNull($exception);
	}

	public function testType(): void
	{
		$args = MultiValueArgs::fromArray($this->rule->resolveArgs([
			ListOfRule::ITEM_RULE => [
				MetaSource::OPTION_TYPE => MixedRule::class,
			],
		], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertInstanceOf(SimpleValueType::class, $type->getItemType());
		self::assertCount(0, $type->getParameters());
	}

	public function testTypeWithArgs(): void
	{
		$args = MultiValueArgs::fromArray($this->rule->resolveArgs([
			ListOfRule::ITEM_RULE => [
				MetaSource::OPTION_TYPE => MixedRule::class,
			],
			ListOfRule::MIN_ITEMS => 10,
			ListOfRule::MAX_ITEMS => 100,
		], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertInstanceOf(SimpleValueType::class, $type->getItemType());

		self::assertCount(2, $type->getParameters());
		self::assertTrue($type->hasParameter(ListOfRule::MIN_ITEMS));
		self::assertSame(10, $type->getParameter(ListOfRule::MIN_ITEMS)->getValue());
		self::assertTrue($type->hasParameter(ListOfRule::MAX_ITEMS));
		self::assertSame(100, $type->getParameter(ListOfRule::MAX_ITEMS)->getValue());
	}

}
