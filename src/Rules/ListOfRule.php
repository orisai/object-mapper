<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

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
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\Utils\Arrays\ArrayMerger;
use function count;
use function is_array;
use function is_int;

/**
 * @extends MultiValueRule<MultiValueArgs>
 */
final class ListOfRule extends MultiValueRule
{

	private const Continuous = 'continuous';

	public function resolveArgs(array $args, MetaFieldContext $context): MultiValueArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::ItemRule, self::MinItems, self::MaxItems, self::MergeDefaults]);

		$resolver = $context->getMetaResolver();

		$checker->checkRequiredArg(self::ItemRule);

		$item = $checker->checkInstanceOf(self::ItemRule, RuleCompileMeta::class);
		$itemRuleMeta = $resolver->resolveRuleMeta($item, $context);

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

		return new MultiValueArgs(
			$itemRuleMeta,
			$minItems,
			$maxItems,
			$mergeDefaults,
		);
	}

	public function getArgsType(): string
	{
		return MultiValueArgs::class;
	}

	/**
	 * @param mixed          $value
	 * @param MultiValueArgs $args
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
		$type = null;

		if (!is_array($value)) {
			$type = $this->createType($args, $services, $dynamic);
			$type->markInvalid();

			throw ValueDoesNotMatch::create($type, Value::of($initValue));
		}

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
		$itemRule = $services->getRule($itemMeta->getType());
		$itemArgs = $itemMeta->getArgs();
		if (!$itemRule instanceof PhasedRule) {
			$itemRule = new PhasedRuleAdapter($itemRule);
			$phasedRule = false;
		} else {
			$phasedRule = true;
		}

		$lastIntKey = -1; // List starts from 0
		foreach ($value as $key => $item) {
			if (!is_int($key) || $key !== ++$lastIntKey) {
				$keyType = $this->createKeyType();
				$keyType->markParameterInvalid(self::Continuous);

				$type ??= $this->createType($args, $services, $dynamic);
				$type->addInvalidKey(
					$key,
					ValueDoesNotMatch::create($keyType, Value::of($key)),
				);
			}

			if (is_int($key)) {
				$lastIntKey = $key;
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
			$value = ArrayMerger::merge($property->getDefaultValue(), $value);
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
		$itemRule = $services->getRule($itemMeta->getType());
		$itemArgs = $itemMeta->getArgs();

		$type = GenericArrayType::forList(
			$this->createKeyType(),
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

	private function createKeyType(): SimpleValueType
	{
		$type = new SimpleValueType('int');
		$type->addKeyParameter(self::Continuous);

		return $type;
	}

}
