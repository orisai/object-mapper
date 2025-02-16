<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Meta\Context\MetaFieldContext;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Types\GenericArrayType;
use Orisai\Utils\Arrays\ArrayMerger;
use function assert;
use function count;
use function get_debug_type;
use function is_array;
use function is_int;
use function is_string;
use function sprintf;

/**
 * @extends MultiValueRule<ArrayOfArgs>
 */
final class ArrayOfRule extends MultiValueRule
{

	public const KeyRule = 'key';

	public function resolveArgs(array $args, MetaFieldContext $context): ArrayOfArgs
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

		if (
			$mergeDefaults
			&& $context->hasDefaultValue()
			&& !is_array($defaultValue = $context->getDefaultValue())
		) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Argument "%s" given to "%s" is set to true but the default value is "%s" insteadof an array.',
					self::MergeDefaults,
					self::class,
					get_debug_type($defaultValue),
				));
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
	public function processValue(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	): array
	{
		$initValue = $value;

		if (!is_array($value)) {
			$type = $this->createType($args, $services, $dynamic);
			$type->markInvalid();

			throw ValueDoesNotMatch::create($type, Value::of($initValue));
		}

		$type = null;

		if ($args->minItems !== null && count($value) < $args->minItems) {
			$type = $this->createType($args, $services, $dynamic);
			$type->markParameterInvalid(self::MinItems);
		}

		if ($args->maxItems !== null && count($value) > $args->maxItems) {
			$type ??= $this->createType($args, $services, $dynamic);
			$type->markParameterInvalid(self::MaxItems);

			throw ValueDoesNotMatch::create($type, Value::of($initValue));
		}

		$itemMeta = $args->itemRuleMeta;
		$itemRule = $services->getRule($itemMeta->type);
		$itemArgs = $itemMeta->args;
		if (!$itemRule instanceof PhasedRule) {
			$itemRule = new PhasedRuleAdapter($itemRule);
			$phasedRule = false;
		} else {
			$phasedRule = true;
		}

		$keyMeta = $args->keyRuleMeta;
		if ($keyMeta !== null) {
			$keyRule = $services->getRule($keyMeta->type);
			$keyArgs = $keyMeta->args;
		} else {
			$keyRule = null;
			$keyArgs = null;
		}

		foreach ($value as $key => $item) {
			if ($keyRule !== null && $keyArgs !== null) {
				try {
					$key = $keyRule->processValue(
						$key,
						$keyArgs,
						$services,
						$property,
						$dynamic->createClone(),
					);
					assert(is_int($key) || is_string($key));
				} catch (ValueDoesNotMatch | InvalidData $exception) {
					$type ??= $this->createType($args, $services, $dynamic);
					$type->addInvalidKey($key, $exception);
				}
			}

			try {
				$value[$key] = $itemRule->processValuePhase1(
					$item,
					$itemArgs,
					$services,
					$property,
					$dynamic->createClone(),
				);
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				$type ??= $this->createType($args, $services, $dynamic);
				$type->addInvalidValue($key, $exception);
				// Remove invalid value because only valid values are expected beyond this point
				// Invalid keys are fine because we don't work them beyond
				unset($value[$key]);
			}
		}

		if ($phasedRule) {
			$itemRule->processValuePhase2(
				$value,
				$args,
				$services,
				$property,
				$dynamic->createClone(),
			);

			foreach ($value as $key => $item) {
				try {
					$value[$key] = $itemRule->processValuePhase3(
						$item,
						$itemArgs,
						$services,
						$property,
						$dynamic->createClone(),
					);
				} catch (ValueDoesNotMatch | InvalidData $exception) {
					$type ??= $this->createType($args, $services, $dynamic);
					$type->addInvalidValue($key, $exception);
				}
			}
		}

		if (
			$type !== null
			&& (
				($hasInvalidParameters = $type->hasInvalidParameters())
				|| $type->hasInvalidPairs()
			)
		) {
			throw ValueDoesNotMatch::create(
				$type,
				$hasInvalidParameters ? Value::of($initValue) : Value::none(),
			);
		}

		if ($args->mergeDefaults && $property->hasDefaultValue()) {
			$default = $property->getDefaultValue();
			assert(is_array($default)); // Rule validates that default is an array
			$value = ArrayMerger::merge($default, $value);
		}

		return $value;
	}

	public function createType(
		Args $args,
		ServicesContext $services,
		DynamicContext $dynamic
	): GenericArrayType
	{
		$itemMeta = $args->itemRuleMeta;
		$itemRule = $services->getRule($itemMeta->type);
		$itemArgs = $itemMeta->args;

		$keyMeta = $args->keyRuleMeta;
		if ($keyMeta !== null) {
			$keyRule = $services->getRule($keyMeta->type);
			$keyArgs = $keyMeta->args;
			$keyType = $keyRule->createType($keyArgs, $services, $dynamic->createClone());
		}

		$type = GenericArrayType::forArray(
			$keyType ?? null,
			$itemRule->createType($itemArgs, $services, $dynamic->createClone()),
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
