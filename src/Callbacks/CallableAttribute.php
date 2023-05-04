<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\BaseAttribute;

/**
 * Base interface for callable annotations
 */
interface CallableAttribute extends BaseAttribute
{

	/**
	 * @return class-string<Callback<Args>>
	 */
	public function getType(): string;

}
