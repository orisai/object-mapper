<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\PhpTypes\CompoundNode;
use Orisai\ObjectMapper\PhpTypes\Node;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\Value;

final class AllOfRule extends CompoundRule
{

	/**
	 * @param mixed        $value
	 * @param CompoundArgs $args
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		$initValue = $value;
		$type = $this->createType($args, $context);
		$anyValidationFailed = false;

		foreach ($args->rules as $key => $nestedRuleMeta) {
			if ($anyValidationFailed) {
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
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				$exception->dropValue(); // May be mutated by rules
				$type->overwriteInvalidSubtype($key, $exception);
				$anyValidationFailed = true;
			}
		}

		if ($anyValidationFailed) {
			throw ValueDoesNotMatch::create($type, Value::of($initValue));
		}

		return $value;
	}

	protected function createCompoundType(): CompoundType
	{
		return CompoundType::createAndType();
	}

	/**
	 * @param CompoundRuleArgs $args
	 */
	public function getExpectedInputType(Args $args, TypeContext $context): Node
	{
		return CompoundNode::createAndType(
			$this->getExpectedInputTypeNodes($args, $context),
		);
	}

	/**
	 * @param CompoundRuleArgs $args
	 */
	public function getReturnType(Args $args, TypeContext $context): Node
	{
		return CompoundNode::createAndType(
			$this->getReturnTypeNodes($args, $context),
		);
	}

}
