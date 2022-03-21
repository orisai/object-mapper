<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Callbacks;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Attributes\BaseAttribute;
use Orisai\ObjectMapper\Callbacks\Callback;

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
