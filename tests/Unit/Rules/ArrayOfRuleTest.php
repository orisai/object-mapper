<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Rules;

use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\DefaultValueMeta;
use Orisai\ObjectMapper\Meta\MetaSource;
use Orisai\ObjectMapper\Rules\ArrayOfArgs;
use Orisai\ObjectMapper\Rules\ArrayOfRule;
use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Rules\StringRule;
use Orisai\ObjectMapper\Types\ArrayType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Tests\Orisai\ObjectMapper\Fixtures\AlwaysInvalidRule;
use Tests\Orisai\ObjectMapper\Toolkit\RuleTestCase;
use function assert;

final class ArrayOfRuleTest extends RuleTestCase
{

	private ArrayOfRule $rule;

	protected function setUp(): void
	{
		parent::setUp();
		$this->rule = new ArrayOfRule();
		$this->ruleManager->addRule(AlwaysInvalidRule::class, new AlwaysInvalidRule());
	}

	public function testProcessValid(): void
	{
		$value = ['foo', 'bar', 'baz', 123];
		$defaults = ['lorem', 'ipsum'];

		$processed = $this->rule->processValue(
			$value,
			ArrayOfArgs::fromArray($this->rule->resolveArgs([
				ArrayOfRule::ITEM_RULE => [MetaSource::OPTION_TYPE => MixedRule::class],
			], $this->ruleArgsContext())),
			$this->fieldContext(DefaultValueMeta::fromValue($defaults)),
		);

		self::assertSame($value, $processed);
	}

	public function testProcessValidWithKeys(): void
	{
		$value = ['a' => 'foo', 'b' => 'bar', 'c' => 'baz', 'd' => 123];

		$processed = $this->rule->processValue(
			$value,
			ArrayOfArgs::fromArray($this->rule->resolveArgs([
				ArrayOfRule::KEY_RULE => [MetaSource::OPTION_TYPE => StringRule::class],
				ArrayOfRule::ITEM_RULE => [MetaSource::OPTION_TYPE => MixedRule::class],
			], $this->ruleArgsContext())),
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
			ArrayOfArgs::fromArray($this->rule->resolveArgs([
				ArrayOfRule::ITEM_RULE => [MetaSource::OPTION_TYPE => MixedRule::class],
				ArrayOfRule::MAX_ITEMS => 5,
				ArrayOfRule::MERGE_DEFAULTS => true,
			], $this->ruleArgsContext())),
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

		try {
			$this->rule->processValue(
				null,
				ArrayOfArgs::fromArray($this->rule->resolveArgs([
					ArrayOfRule::ITEM_RULE => [MetaSource::OPTION_TYPE => MixedRule::class],
				], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof ArrayType);

			self::assertTrue($type->isInvalid());
		}

		self::assertNotNull($exception);
	}

	public function testProcessInvalidParameterMinAndInvalidValuesAndKeys(): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				['foo' => 'bar', 'baz' => 123, 10 => 456, 11 => 'test'],
				ArrayOfArgs::fromArray($this->rule->resolveArgs([
					ArrayOfRule::KEY_RULE => [MetaSource::OPTION_TYPE => StringRule::class],
					ArrayOfRule::ITEM_RULE => [MetaSource::OPTION_TYPE => StringRule::class],
					ArrayOfRule::MIN_ITEMS => 10,
				], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof ArrayType);

			self::assertFalse($type->isInvalid());
			self::assertTrue($type->getParameter(ArrayOfRule::MIN_ITEMS)->isInvalid());

			self::assertTrue($type->hasInvalidPairs());
			$invalidPairs = $type->getInvalidPairs();
			self::assertCount(3, $invalidPairs);

			[$key, $value] = $invalidPairs['baz'];
			self::assertNull($key);
			self::assertNotNull($value);

			[$key, $value] = $invalidPairs[10];
			self::assertNotNull($key);
			self::assertNotNull($value);

			[$key, $value] = $invalidPairs[11];
			self::assertNotNull($key);
			self::assertNull($value);
		}

		self::assertNotNull($exception);
	}

	public function testProcessInvalidParameterMax(): void
	{
		$exception = null;

		try {
			$this->rule->processValue(
				['foo', 3 => 'bar', 'baz', 123, 456],
				ArrayOfArgs::fromArray($this->rule->resolveArgs([
					ArrayOfRule::ITEM_RULE => [MetaSource::OPTION_TYPE => StringRule::class],
					ArrayOfRule::MAX_ITEMS => 2,
				], $this->ruleArgsContext())),
				$this->fieldContext(),
			);
		} catch (ValueDoesNotMatch $exception) {
			$type = $exception->getInvalidType();
			assert($type instanceof ArrayType);

			self::assertFalse($type->isInvalid());
			self::assertFalse($type->hasParameter(ArrayOfRule::MIN_ITEMS));
			self::assertTrue($type->getParameter(ArrayOfRule::MAX_ITEMS)->isInvalid());
			self::assertFalse($type->hasInvalidPairs());
		}

		self::assertNotNull($exception);
	}

	public function testType(): void
	{
		$args = ArrayOfArgs::fromArray($this->rule->resolveArgs([
			ArrayOfRule::ITEM_RULE => [
				MetaSource::OPTION_TYPE => MixedRule::class,
			],
		], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

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
		$args = ArrayOfArgs::fromArray($this->rule->resolveArgs([
			ArrayOfRule::ITEM_RULE => [
				MetaSource::OPTION_TYPE => MixedRule::class,
			],
			ArrayOfRule::KEY_RULE => [
				MetaSource::OPTION_TYPE => StringRule::class,
			],
			ArrayOfRule::MIN_ITEMS => 10,
			ArrayOfRule::MAX_ITEMS => 100,
		], $this->ruleArgsContext()));

		$type = $this->rule->createType($args, $this->typeContext);

		self::assertEquals(
			$this->rule->createType($args, $this->fieldContext()),
			$type,
		);

		self::assertInstanceOf(SimpleValueType::class, $type->getItemType());
		self::assertInstanceOf(SimpleValueType::class, $type->getKeyType());

		self::assertCount(2, $type->getParameters());
		self::assertTrue($type->hasParameter(ArrayOfRule::MIN_ITEMS));
		self::assertSame(10, $type->getParameter(ArrayOfRule::MIN_ITEMS)->getValue());
		self::assertTrue($type->hasParameter(ArrayOfRule::MAX_ITEMS));
		self::assertSame(100, $type->getParameter(ArrayOfRule::MAX_ITEMS)->getValue());
	}

}
