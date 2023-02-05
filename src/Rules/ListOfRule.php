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
use Orisai\ObjectMapper\Types\GenericArrayType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Value;
use Orisai\Utils\Arrays\ArrayMerger;
use function array_values;
use function count;
use function is_array;
use function is_int;

/**
 * @extends MultiValueRule<MultiValueArgs>
 */
final class ListOfRule extends MultiValueRule
{

	private const Continuous = 'continuous';

	public function resolveArgs(array $args, RuleArgsContext $context): MultiValueArgs
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

		$lastIntKey = -1; // List starts from 0
		if ($itemRule instanceof MultiValueEfficientRule) {
			foreach ($value as $key => $item) {
				if (!is_int($key) || $key !== ++$lastIntKey) {
					$keyType = $this->createKeyType();
					$keyType->markParameterInvalid(self::Continuous);

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
						$context->createClone(),
					);
				} catch (ValueDoesNotMatch | InvalidData $exception) {
					unset($value[$key]);
					$type->addInvalidValue($key, $exception);
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
					$type->addInvalidPair($key, null, $exception);
				}
			}
		} else {
			foreach ($value as $key => $item) {
				if (!is_int($key) || $key !== ++$lastIntKey) {
					$keyType = $this->createKeyType();
					$keyType->markParameterInvalid(self::Continuous);

					$type->addInvalidKey(
						$key,
						ValueDoesNotMatch::create($keyType, Value::of($key)),
					);
				}

				if (is_int($key)) {
					$lastIntKey = $key;
				}

				try {
					$value[$key] = $itemRule->processValue(
						$item,
						$itemArgs,
						$context->createClone(),
					);
				} catch (ValueDoesNotMatch | InvalidData $exception) {
					$type->addInvalidValue($key, $exception);
				}
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
	 * @param MultiValueArgs $args
	 */
	public function createType(Args $args, TypeContext $context): GenericArrayType
	{
		$itemMeta = $args->itemRuleMeta;
		$itemRule = $context->getRule($itemMeta->getType());
		$itemArgs = $itemMeta->getArgs();

		$type = GenericArrayType::forList(
			$this->createKeyType(),
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

	private function createKeyType(): SimpleValueType
	{
		$type = new SimpleValueType('int');
		$type->addKeyParameter(self::Continuous);

		return $type;
	}

}
