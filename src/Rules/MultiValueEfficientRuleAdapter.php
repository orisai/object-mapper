<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\NotImplemented;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Types\Type;

/**
 * @implements MultiValueEfficientRule<Args>
 */
final class MultiValueEfficientRuleAdapter implements MultiValueEfficientRule
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

	public function resolveArgs(array $args, RuleArgsContext $context): Args
	{
		$this->throwNotImplemented(__FUNCTION__);
	}

	public function getArgsType(): string
	{
		$this->throwNotImplemented(__FUNCTION__);
	}

	public function processValuePhase1($value, Args $args, FieldContext $context)
	{
		return $this->rule->processValue($value, $args, $context);
	}

	public function processValuePhase2(array $values, Args $args, FieldContext $context): void
	{
		// Noop
	}

	public function processValuePhase3($value, Args $args, FieldContext $context)
	{
		return $value;
	}

	public function processValue($value, Args $args, FieldContext $context): void
	{
		$this->throwNotImplemented(__FUNCTION__);
	}

	public function createType(Args $args, TypeContext $context): Type
	{
		return $this->rule->createType($args, $context);
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
