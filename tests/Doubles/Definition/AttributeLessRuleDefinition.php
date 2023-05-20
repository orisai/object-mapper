<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Definition;

use Orisai\ObjectMapper\Rules\MixedRule;
use Orisai\ObjectMapper\Rules\RuleDefinition;

/**
 * @implements RuleDefinition<MixedRule>
 */
final class AttributeLessRuleDefinition implements RuleDefinition
{

	public function getType(): string
	{
		return MixedRule::class;
	}

	public function getArgs(): array
	{
		return [];
	}

}
