<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\CompoundTypeOperator;

final class AllOfRule extends CompoundRule
{

	/**
	 * @param mixed        $value
	 * @param CompoundArgs $args
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 */
	public function processValue(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	)
	{
		$initValue = $value;
		$type = null;
		$anyValidationFailed = false;

		foreach ($args->rules as $key => $nestedRuleMeta) {
			if ($anyValidationFailed) {
				$type ??= $this->createType($args, $services, $dynamic);
				$type->setSubtypeSkipped($key);

				continue;
			}

			$nestedRule = $services->getRule($nestedRuleMeta->getType());
			$nestedRuleArgs = $nestedRuleMeta->getArgs();

			try {
				$value = $nestedRule->processValue(
					$value,
					$nestedRuleArgs,
					$services,
					$property,
					$dynamic->createClone(),
				);
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				$exception->dropValue(); // May be mutated by rules
				$type ??= $this->createType($args, $services, $dynamic);
				$type->overwriteInvalidSubtype($key, $exception);
				$anyValidationFailed = true;
			}
		}

		if ($type !== null && $anyValidationFailed) {
			throw ValueDoesNotMatch::create($type, Value::of($initValue));
		}

		return $value;
	}

	protected function createCompoundType(): CompoundType
	{
		return new CompoundType(CompoundTypeOperator::and());
	}

}
