<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Orisai\ObjectMapper\Meta\MetaDefinition;

/**
 * @template-covariant T of Callback
 */
interface CallbackDefinition extends MetaDefinition
{

	/**
	 * @return class-string<T>
	 */
	public function getType(): string;

}
