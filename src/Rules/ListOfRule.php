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
use Orisai\ObjectMapper\Types\ListType;
use function array_keys;
use function array_values;
use function count;
use function is_array;

/**
 * @implements Rule<MultiValueArgs>
 */
final class ListOfRule extends MultiValueRule
{

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::ITEM_TYPE, self::MIN_ITEMS, self::MAX_ITEMS, self::MERGE_DEFAULTS]);

		$resolver = $context->getMetaResolver();

		$checker->checkRequiredArg(self::ITEM_TYPE);
		$item = $checker->checkArray(self::ITEM_TYPE);
		$args[self::ITEM_TYPE] = $resolver->resolveRuleMeta($item, $context);

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
		return MultiValueArgs::class;
	}

	/**
	 * @param mixed $value
	 * @param MultiValueArgs $args
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

		if (array_keys($value) !== array_keys(array_values($value))) {
			$type->markKeysInvalid();
		}

		$itemMeta = $args->itemType;
		$itemRule = $context->getRule($itemMeta->getType());
		$itemArgs = $this->createRuleArgsInst($itemRule, $itemMeta);

		foreach ($value as $key => $item) {
			try {
				$value[$key] = $itemRule->processValue(
					$item,
					$itemArgs,
					$context,
				);
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				//TODO - mark key invalid (if not just one higher than previous or if it's first and not 0)
				$type->addInvalidItem($key, $exception->getInvalidType());
			}
		}

		if ($type->hasInvalidParameters() || $type->hasInvalidItems()) {
			throw ValueDoesNotMatch::create($type);
		}

		if ($args->mergeDefaults && $context->hasDefaultValue()) {
			$value = Merger::merge($value, $context->getDefaultValue());
		}

		return $value;
	}

	/**
	 * @param MultiValueArgs $args
	 */
	public function createType(Args $args, TypeContext $context): ListType
	{
		$itemMeta = $args->itemType;
		$itemRule = $context->getRule($itemMeta->getType());
		$itemArgs = $this->createRuleArgsInst($itemRule, $itemMeta);

		$parameters = [
			'minItems' => $args->minItems,
			'maxItems' => $args->maxItems,
		];

		return new ListType(
			$itemRule->createType($itemArgs, $context),
			$parameters,
		);
	}

}
