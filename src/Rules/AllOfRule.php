<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\Types\CompoundType;

final class AllOfRule extends CompoundRule
{

	/**
	 * @param mixed $value
	 * @param CompoundRuleArgs $args
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		$type = $this->createType($args, $context);
		$anyValidationFailed = false;

		foreach ($args->rules as $key => $nestedRuleMeta) {
			if ($anyValidationFailed) {
				$type->setSubtypeSkipped($key);

				continue;
			}

			$nestedRule = $context->getRule($nestedRuleMeta->getType());
			$nestedRuleArgs = $this->createRuleArgsInst($nestedRule, $nestedRuleMeta);

			try {
				$value = $nestedRule->processValue(
					$value,
					$nestedRuleArgs,
					$context,
				);
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				$type->overwriteInvalidSubtype($key, $exception->getInvalidType());
				$anyValidationFailed = true;
			}
		}

		if ($anyValidationFailed) {
			throw ValueDoesNotMatch::create($type);
		}

		return $value;
	}

	protected function getOperator(): string
	{
		return CompoundType::OPERATOR_AND;
	}

}
