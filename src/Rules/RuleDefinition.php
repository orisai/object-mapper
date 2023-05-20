<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\MetaDefinition;

interface RuleDefinition extends MetaDefinition
{

	/**
	 * @return class-string<Rule<Args>>
	 */
	public function getType(): string;

}
