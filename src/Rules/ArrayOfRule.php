<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Types\GenericArrayType;
use Orisai\Utils\Arrays\ArrayMerger;
use function array_values;
use function count;
use function is_array;

/**
 * @extends MultiValueRule<ArrayOfArgs>
 */
final class ArrayOfRule extends MultiValueRule
{

	/** @internal */
	public const KeyRule = 'key';

	public function resolveArgs(array $args, RuleArgsContext $context): ArrayOfArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs(
			[self::KeyRule, self::ItemRule, self::MinItems, self::MaxItems, self::MergeDefaults],
		);

		$resolver = $context->getMetaResolver();

		$checker->checkRequiredArg(self::ItemRule);
		$item = $checker->checkInstanceOf(self::ItemRule, RuleCompileMeta::class);
		$itemRuleMeta = $resolver->resolveRuleMeta($item, $context);

		$keyRuleMeta = null;
		if ($checker->hasArg(self::KeyRule)) {
			$key = $checker->checkNullableInstanceOf(self::KeyRule, RuleCompileMeta::class);

			if ($key !== null) {
				$keyRuleMeta = $resolver->resolveRuleMeta($key, $context);
			}
		}

		$minItems = null;
		if ($checker->hasArg(self::MinItems)) {
			$minItems = $checker->checkNullableInt(self::MinItems);
		}

		$maxItems = null;
		if ($checker->hasArg(self::MaxItems)) {
			$maxItems = $checker->checkNullableInt(self::MaxItems);
		}

		$mergeDefaults = false;
		if ($checker->hasArg(self::MergeDefaults)) {
			$mergeDefaults = $checker->checkBool(self::MergeDefaults);
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
	 * @param mixed       $value
	 * @param ArrayOfArgs $args
	 * @return array<mixed>
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context): array
	{
		$initValue = $value;
		$type = $this->createType($args, $context);

		if (!is_array($value)) {
			$type->markInvalid();

			throw ValueDoesNotMatch::create($type, Value::of($initValue));
		}

		if ($args->minItems !== null && count($value) < $args->minItems) {
			$type->markParameterInvalid(self::MinItems);
		}

		if ($args->maxItems !== null && count($value) > $args->maxItems) {
			$type->markParameterInvalid(self::MaxItems);

			throw ValueDoesNotMatch::create($type, Value::of($initValue));
		}

		$itemMeta = $args->itemRuleMeta;
		$itemRule = $context->getRule($itemMeta->getType());
		$itemArgs = $itemMeta->getArgs();
		if (!$itemRule instanceof MultiValueEfficientRule) {
			$itemRule = new MultiValueEfficientRuleAdapter($itemRule);
		}

		$keyMeta = $args->keyRuleMeta;
		if ($keyMeta !== null) {
			$keyRule = $context->getRule($keyMeta->getType());
			$keyArgs = $keyMeta->getArgs();
		} else {
			$keyRule = null;
			$keyArgs = null;
		}

		foreach ($value as $key => $item) {
			if ($keyRule !== null && $keyArgs !== null) {
				try {
					$key = $keyRule->processValue($key, $keyArgs, $context->createClone());
				} catch (ValueDoesNotMatch | InvalidData $exception) {
					$type->addInvalidKey($key, $exception);
				}
			}

			try {
				$value[$key] = $itemRule->processValuePhase1(
					$item,
					$itemArgs,
					$context->createClone(),
				);
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				$type->addInvalidValue($key, $exception);
			}

			if (isset($type->getInvalidPairs()[$key])) {
				unset($value[$key]);
			}
		}

		$itemRule->processValuePhase2(array_values($value), $args, $context->createClone());

		foreach ($value as $key => $item) {
			try {
				$value[$key] = $itemRule->processValuePhase3(
					$item,
					$itemArgs,
					$context->createClone(),
				);
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				$type->addInvalidValue($key, $exception);
			}
		}

		$hasInvalidParameters = $type->hasInvalidParameters();
		if ($hasInvalidParameters || $type->hasInvalidPairs()) {
			throw ValueDoesNotMatch::create(
				$type,
				$hasInvalidParameters ? Value::of($initValue) : Value::none(),
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
	public function createType(Args $args, TypeContext $context): GenericArrayType
	{
		$itemMeta = $args->itemRuleMeta;
		$itemRule = $context->getRule($itemMeta->getType());
		$itemArgs = $itemMeta->getArgs();

		$keyMeta = $args->keyRuleMeta;
		if ($keyMeta !== null) {
			$keyRule = $context->getRule($keyMeta->getType());
			$keyArgs = $keyMeta->getArgs();
			$keyType = $keyRule->createType($keyArgs, $context->createClone());
		}

		$type = GenericArrayType::forArray(
			$keyType ?? null,
			$itemRule->createType($itemArgs, $context->createClone()),
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
