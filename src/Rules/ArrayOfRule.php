<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exceptions\InvalidData;
use Orisai\ObjectMapper\Exceptions\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Types\ArrayType;
use Orisai\ObjectMapper\Types\NoValue;
use Orisai\Utils\Arrays\ArrayMerger;
use function count;
use function is_array;

/**
 * @phpstan-extends MultiValueRule<ArrayOfArgs>
 */
final class ArrayOfRule extends MultiValueRule
{

	public const KEY_RULE = 'key';

	/**
	 * {@inheritDoc}
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): ArrayOfArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs(
			[self::KEY_RULE, self::ITEM_RULE, self::MIN_ITEMS, self::MAX_ITEMS, self::MERGE_DEFAULTS],
		);

		$resolver = $context->getMetaResolver();

		$checker->checkRequiredArg(self::ITEM_RULE);
		$item = $checker->checkInstanceOf(self::ITEM_RULE, RuleCompileMeta::class);
		$itemRuleMeta = $resolver->resolveRuleMeta($item, $context);

		$keyRuleMeta = null;
		if ($checker->hasArg(self::KEY_RULE)) {
			$key = $checker->checkNullableInstanceOf(self::KEY_RULE, RuleCompileMeta::class);

			if ($key !== null) {
				$keyRuleMeta = $resolver->resolveRuleMeta($key, $context);
			}
		}

		$minItems = null;
		if ($checker->hasArg(self::MIN_ITEMS)) {
			$minItems = $checker->checkNullableInt(self::MIN_ITEMS);
		}

		$maxItems = null;
		if ($checker->hasArg(self::MAX_ITEMS)) {
			$maxItems = $checker->checkNullableInt(self::MAX_ITEMS);
		}

		$mergeDefaults = false;
		if ($checker->hasArg(self::MERGE_DEFAULTS)) {
			$mergeDefaults = $checker->checkBool(self::MERGE_DEFAULTS);
		}

		return new ArrayOfArgs(
			$itemRuleMeta,
			$keyRuleMeta,
			$minItems,
			$maxItems,
			$mergeDefaults,
		);
	}

	public function getArgsType(): string
	{
		return ArrayOfArgs::class;
	}

	/**
	 * @param mixed $value
	 * @param ArrayOfArgs $args
	 * @return array<mixed>
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context): array
	{
		$type = $this->createType($args, $context);

		if (!is_array($value)) {
			$type->markInvalid();

			throw ValueDoesNotMatch::create($type, $value);
		}

		if ($args->minItems !== null && count($value) < $args->minItems) {
			$type->markParameterInvalid(self::MIN_ITEMS);
		}

		if ($args->maxItems !== null && count($value) > $args->maxItems) {
			$type->markParameterInvalid(self::MAX_ITEMS);

			throw ValueDoesNotMatch::create($type, $value);
		}

		$itemMeta = $args->itemRuleMeta;
		$itemRule = $context->getRule($itemMeta->getType());
		$itemArgs = $itemMeta->getArgs();

		$keyMeta = $args->keyRuleMeta;
		if ($keyMeta !== null) {
			$keyRule = $context->getRule($keyMeta->getType());
			$keyArgs = $keyMeta->getArgs();
		} else {
			$keyRule = null;
			$keyArgs = null;
		}

		foreach ($value as $key => $item) {
			$keyException = null;
			$itemException = null;

			if ($keyRule !== null && $keyArgs !== null) {
				try {
					$key = $keyRule->processValue($key, $keyArgs, $context);
				} catch (ValueDoesNotMatch | InvalidData $exception) {
					$keyException = $exception;
				}
			}

			try {
				$value[$key] = $itemRule->processValue(
					$item,
					$itemArgs,
					$context,
				);
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				$itemException = $exception;
			}

			if ($itemException !== null || $keyException !== null) {
				$type->addInvalidPair($key, $keyException, $itemException);
			}
		}

		$hasInvalidParameters = $type->hasInvalidParameters();
		if ($hasInvalidParameters || $type->hasInvalidPairs()) {
			throw ValueDoesNotMatch::create(
				$type,
				$hasInvalidParameters ? $value : NoValue::create(),
			);
		}

		if ($args->mergeDefaults && $context->hasDefaultValue()) {
			$value = ArrayMerger::merge($context->getDefaultValue(), $value);
		}

		return $value;
	}

	/**
	 * @param ArrayOfArgs $args
	 */
	public function createType(Args $args, TypeContext $context): ArrayType
	{
		$itemMeta = $args->itemRuleMeta;
		$itemRule = $context->getRule($itemMeta->getType());
		$itemArgs = $itemMeta->getArgs();

		$keyMeta = $args->keyRuleMeta;
		if ($keyMeta !== null) {
			$keyRule = $context->getRule($keyMeta->getType());
			$keyArgs = $keyMeta->getArgs();
			$keyType = $keyRule->createType($keyArgs, $context);
		}

		$type = new ArrayType(
			$keyType ?? null,
			$itemRule->createType($itemArgs, $context),
		);

		if ($args->minItems !== null) {
			$type->addKeyValueParameter('minItems', $args->minItems);
		}

		if ($args->maxItems !== null) {
			$type->addKeyValueParameter('maxItems', $args->maxItems);
		}

		return $type;
	}

}
