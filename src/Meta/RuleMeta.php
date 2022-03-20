<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Rules\CompoundRule;
use Orisai\ObjectMapper\Rules\Rule;
use function is_a;
use function is_string;

final class RuleMeta
{

	/** @var class-string<Rule<Args>> */
	private string $type;

	/** @var array<mixed> */
	private array $args;

	/**
	 * @param class-string<Rule<Args>> $type
	 * @param array<mixed>             $args
	 */
	public function __construct(string $type, array $args = [])
	{
		$this->type = $type;
		$this->args = $args;
	}

	/**
	 * @return class-string<Rule<Args>>
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return $this->args;
	}

	/**
	 * @param class-string<Rule<Args>>|array<class-string<Rule<Args>>> $type
	 */
	public function mayContainRuleType($type): bool
	{
		return $this->mayContainRuleTypeInternal(is_string($type) ? [$type] : $type, $this);
	}

	/**
	 * @param array<class-string<Rule<Args>>> $types
	 */
	private function mayContainRuleTypeInternal(array $types, RuleMeta $ruleNode): bool
	{
		$nodeType = $ruleNode->getType();

		foreach ($types as $possibleType) {
			if (is_a($nodeType, $possibleType, true)) {
				return true;
			}
		}

		if (is_a($nodeType, CompoundRule::class, true)) {
			$nodeArgs = $ruleNode->getArgs();

			foreach ($nodeArgs[CompoundRule::RULES] as $nestedRuleMeta) {
				if ($this->mayContainRuleTypeInternal($types, $nestedRuleMeta)) {
					return true;
				}
			}
		}

		return false;
	}

}
