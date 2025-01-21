<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\NotImplemented;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Context\MetaFieldContext;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Types\Type;

/**
 * @implements PhasedRule<Args>
 *
 * @internal
 */
final class PhasedRuleAdapter implements PhasedRule
{

	/** @var Rule<Args> */
	private Rule $rule;

	/**
	 * @param Rule<Args> $rule
	 */
	public function __construct(Rule $rule)
	{
		$this->rule = $rule;
	}

	public function resolveArgs(array $args, MetaFieldContext $context): Args
	{
		$this->throwNotImplemented(__FUNCTION__);
	}

	public function getArgsType(): string
	{
		$this->throwNotImplemented(__FUNCTION__);
	}

	public function processValuePhase1(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	)
	{
		return $this->rule->processValue($value, $args, $services, $property, $dynamic);
	}

	public function processValuePhase2(
		array $values,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	): void
	{
		// Noop
	}

	public function processValuePhase3(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	)
	{
		return $value;
	}

	public function processValue(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	): void
	{
		$this->throwNotImplemented(__FUNCTION__);
	}

	public function createType(
		Args $args,
		ServicesContext $services,
		DynamicContext $dynamic
	): Type
	{
		return $this->rule->createType($args, $services, $dynamic);
	}

	/**
	 * @return never
	 */
	private function throwNotImplemented(string $method): void
	{
		throw NotImplemented::create()
			->withMessage(
				"Method '$method()' should never be called, adapter is used internally at runtime for phased processing.",
			);
	}

}
