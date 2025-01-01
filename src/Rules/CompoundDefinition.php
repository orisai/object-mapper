<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;

abstract class CompoundDefinition implements RuleDefinition
{

	/** @var array<RuleCompileMeta> */
	private array $rules;

	/**
	 * @param list<RuleDefinition> $definitions
	 */
	public function __construct(array $definitions)
	{
		$this->rules = $this->definitionsToRules($definitions);
	}

	/**
	 * @param list<RuleDefinition> $definitions
	 * @return array<RuleCompileMeta>
	 */
	private function definitionsToRules(array $definitions): array
	{
		$rules = [];
		foreach ($definitions as $key => $definition) {
			$rules[$key] = new RuleCompileMeta($definition->getType(), $definition->getArgs());
		}

		return $rules;
	}

	public function getArgs(): array
	{
		return [
			CompoundRule::Rules => $this->rules,
		];
	}

}
