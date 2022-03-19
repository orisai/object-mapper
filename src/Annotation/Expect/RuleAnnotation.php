<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Orisai\ObjectMapper\Annotation\BaseAnnotation;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Rules\Rule;

/**
 * Base interface for rule annotations
 */
interface RuleAnnotation extends BaseAnnotation
{

	/**
	 * @return class-string<Rule<Args>>
	 */
	public function getType(): string;

}
