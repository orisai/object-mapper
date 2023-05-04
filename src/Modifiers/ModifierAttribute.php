<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Modifiers;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Attributes\BaseAttribute;

interface ModifierAttribute extends BaseAttribute
{

	/**
	 * @return class-string<Modifier<Args>>
	 */
	public function getType(): string;

}
