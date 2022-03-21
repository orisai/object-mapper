<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Expect;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Attributes\BaseAttribute;
use Orisai\ObjectMapper\Rules\Rule;

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
