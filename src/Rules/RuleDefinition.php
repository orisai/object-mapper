<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Meta\MetaDefinition;

/**
 * @template-covariant T of Rule
 */
interface RuleDefinition extends MetaDefinition
{

	/**
	 * @return class-string<T>
	 */
	public function getType(): string;

}
