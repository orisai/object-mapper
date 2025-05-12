<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\MetaDefinition;

abstract class RuleDefinition implements MetaDefinition
{

	final public function getScope(): string
	{
		return Rule::class;
	}

	/**
	 * @return class-string<Rule<Args>>
	 */
	abstract public function getHandler(): string;

}
