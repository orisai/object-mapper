<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Rules\CompoundRule;
use Orisai\ObjectMapper\Rules\Rule;
use function is_a;

final class RuleCompileMeta
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
	 * @param array<class-string<Rule<Args>>> $type
	 */
	public function containsAnyOfRules(array $type): bool
	{
		return $this->containsAnyOfRulesInternal($type, $this);
	}

	/**
	 * @param array<class-string<Rule<Args>>> $types
	 */
	private function containsAnyOfRulesInternal(array $types, RuleCompileMeta $ruleNode): bool
	{
		$nodeType = $ruleNode->getType();

		foreach ($types as $possibleType) {
			if (is_a($nodeType, $possibleType, true)) {
				return true;
			}
		}

		if (is_a($nodeType, CompoundRule::class, true)) {
			$nodeArgs = $ruleNode->getArgs();

			foreach ($nodeArgs[CompoundRule::Rules] as $nestedRuleMeta) {
				if ($this->containsAnyOfRulesInternal($types, $nestedRuleMeta)) {
					return true;
				}
			}
		}

		return false;
	}

}
