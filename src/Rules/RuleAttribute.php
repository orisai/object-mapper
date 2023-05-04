<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\BaseAttribute;

/**
 * Base interface for rule annotations
 */
interface RuleAttribute extends BaseAttribute
{

	/**
	 * @return class-string<Rule<Args>>
	 */
	public function getType(): string;

}
