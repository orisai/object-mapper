<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

abstract class CompoundDefinition extends RuleDefinition
{

	/** @var array<RuleDefinition> */
	private array $rules;

	/**
	 * @param list<RuleDefinition> $definitions
	 */
	public function __construct(array $definitions)
	{
		$this->rules = $definitions;
	}

	public function getArgs(): array
	{
		return [
			CompoundRule::Rules => $this->rules,
		];
	}

}
