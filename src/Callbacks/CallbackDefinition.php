<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\MetaDefinition;

abstract class CallbackDefinition implements MetaDefinition
{

	/**
	 * @return class-string<Callback<Args>>
	 */
	abstract public function getHandler(): string;

}
