<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\Value;

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
			$nestedRuleArgs = $nestedRuleMeta->getArgs();

			try {
				$value = $nestedRule->processValue(
					$value,
					$nestedRuleArgs,
					$context,
				);

				$anyValidationSucceeded = true;
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				$type->overwriteInvalidSubtype($key, $exception);
			}
		}

		if (!$anyValidationSucceeded) {
			throw ValueDoesNotMatch::create($type, Value::none());
		}

		return $value;
	}

	protected function createCompoundType(): CompoundType
	{
		return CompoundType::createOrType();
	}

}
