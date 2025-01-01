<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\CompoundTypeOperator;

final class AnyOfRule extends CompoundRule
{

	/**
	 * @param mixed        $value
	 * @param CompoundArgs $args
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		$type = null;
		$anyValidationSucceeded = false;

		foreach ($args->rules as $key => $nestedRuleMeta) {
			if ($anyValidationSucceeded) {
				$type ??= $this->createType($args, $context);
				$type->setSubtypeSkipped($key);

				continue;
			}

			$nestedRule = $context->getRule($nestedRuleMeta->getType());
			$nestedRuleArgs = $nestedRuleMeta->getArgs();

			try {
				$value = $nestedRule->processValue(
					$value,
					$nestedRuleArgs,
					$context->createClone(),
				);

				$anyValidationSucceeded = true;
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				$type ??= $this->createType($args, $context);
				$type->overwriteInvalidSubtype($key, $exception);
			}
		}

		if ($type !== null && !$anyValidationSucceeded) {
			throw ValueDoesNotMatch::create($type, Value::none());
		}

		return $value;
	}

	protected function createCompoundType(): CompoundType
	{
		return new CompoundType(CompoundTypeOperator::or());
	}

}
