<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\NoValue;
use Orisai\ObjectMapper\Types\CompoundType;

final class AnyOfRule extends CompoundRule
{

	/**
	 * @param mixed            $value
	 * @param CompoundRuleArgs $args
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		$type = $this->createType($args, $context);
		$anyValidationSucceeded = false;

		foreach ($args->rules as $key => $nestedRuleMeta) {
			if ($anyValidationSucceeded) {
				$type->setSubtypeSkipped($key);

				continue;
			}

			$nestedRule = $context->getRule($nestedRuleMeta->getType());
			$nestedRuleArgs = $this->createRuleArgsInst($nestedRule, $nestedRuleMeta);

			try {
				// the $value will not be mutated if exception occurs
				$value = $nestedRule->processValue(
					$value,
					$nestedRuleArgs,
					$context,
				);

				// only $value that is valid is mutated
				$anyValidationSucceeded = true;
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				// exception will always contain original value
				$type->overwriteInvalidSubtype($key, $exception);
			}
		}

		if (!$anyValidationSucceeded) {
			throw ValueDoesNotMatch::create($type, NoValue::create());
		}

		return $value;
	}

	protected function getOperator(): string
	{
		return CompoundType::OPERATOR_OR;
	}

}
