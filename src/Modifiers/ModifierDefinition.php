<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Modifiers;

use Orisai\ObjectMapper\Meta\MetaDefinition;

/**
 * @template-covariant T of Modifier
 */
interface ModifierDefinition extends MetaDefinition
{

	/**
	 * @return class-string<T>
	 */
	public function getType(): string;

}
