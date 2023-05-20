<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\MetaDefinition;

interface CallbackDefinition extends MetaDefinition
{

	/**
	 * @return class-string<Callback<Args>>
	 */
	public function getType(): string;

}
