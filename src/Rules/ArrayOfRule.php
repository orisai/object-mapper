<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Contributte\Utils\Merger;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\Meta\ArgsChecker;
use Orisai\ObjectMapper\Types\ArrayType;
use function count;
use function is_array;

/**
 * @implements Rule<ArrayOfArgs>
 */
final class ArrayOfRule extends MultiValueRule
{

	public const KEY_TYPE = 'keyType';

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::KEY_TYPE, self::ITEM_TYPE, self::MIN_ITEMS, self::MAX_ITEMS, self::MERGE_DEFAULTS]);

		$resolver = $context->getMetaResolver();

		$checker->checkRequiredArg(self::ITEM_TYPE);
		$item = $checker->checkArray(self::ITEM_TYPE);
		$args[self::ITEM_TYPE] = $resolver->resolveRuleMeta($item, $context);

		if ($checker->hasArg(self::KEY_TYPE)) {
			$key = $checker->checkNullableArray(self::KEY_TYPE);

			if ($key !== null) {
				$args[self::KEY_TYPE] = $resolver->resolveRuleMeta($key, $context);
			}
		}

		if ($checker->hasArg(self::MIN_ITEMS)) {
			$checker->checkNullableInt(self::MIN_ITEMS);
		}

		if ($checker->hasArg(self::MAX_ITEMS)) {
			$checker->checkNullableInt(self::MAX_ITEMS);
		}

		if ($checker->hasArg(self::MERGE_DEFAULTS)) {
			$checker->checkBool(self::MERGE_DEFAULTS);
		}

		return $args;
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

			throw ValueDoesNotMatch::create($type);
		}

		if ($args->minItems !== null && count($value) < $args->minItems) {
			$type->markParameterInvalid(self::MIN_ITEMS);
		}

		if ($args->maxItems !== null && count($value) > $args->maxItems) {
			$type->markParameterInvalid(self::MAX_ITEMS);

			throw ValueDoesNotMatch::create($type);
		}

		$itemMeta = $args->itemType;
		$itemRule = $context->getRule($itemMeta->getType());
		$itemArgs = $this->createRuleArgsInst($itemRule, $itemMeta);

		$keyMeta = $args->keyType;
		if ($keyMeta !== null) {
			$keyRule = $context->getRule($keyMeta->getType());
			$keyArgs = $this->createRuleArgsInst($keyRule, $keyMeta);
		} else {
			$keyRule = null;
			$keyArgs = null;
		}

		foreach ($value as $key => $item) {
			$keyException = null;
			$itemException = null;

			if ($keyRule !== null && $keyArgs !== null) {
				try {
					$key = $keyRule->processValue(
						$key,
						$keyArgs,
						$context,
					);
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
				$type->addInvalidPair(
					$key,
					$keyException !== null ? $keyException->getInvalidType() : null,
					$itemException !== null ? $itemException->getInvalidType() : null,
				);
			}
		}

		if ($type->hasInvalidParameters() || $type->hasInvalidPairs()) {
			throw ValueDoesNotMatch::create($type);
		}

		if ($args->mergeDefaults && $context->hasDefaultValue()) {
			$value = Merger::merge($value, $context->getDefaultValue());
		}

		return $value;
	}

	/**
	 * @param ArrayOfArgs $args
	 */
	public function createType(Args $args, TypeContext $context): ArrayType
	{
		$itemMeta = $args->itemType;
		$itemRule = $context->getRule($itemMeta->getType());
		$itemArgs = $this->createRuleArgsInst($itemRule, $itemMeta);

		$keyMeta = $args->keyType;
		if ($keyMeta !== null) {
			$keyRule = $context->getRule($keyMeta->getType());
			$keyArgs = $this->createRuleArgsInst($keyRule, $keyMeta);
			$keyType = $keyRule->createType($keyArgs, $context);
		}

		$parameters = [
			'minItems' => $args->minItems,
			'maxItems' => $args->maxItems,
		];

		return new ArrayType(
			$keyType ?? null,
			$itemRule->createType($itemArgs, $context),
			$parameters,
		);
	}

}
