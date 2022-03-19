<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Modifiers;

use Orisai\ObjectMapper\Annotation\BaseAnnotation;
use Orisai\ObjectMapper\Modifiers\Modifier;

interface ModifierAnnotation extends BaseAnnotation
{

	/**
	 * @return class-string<Modifier>
	 */
	public function getType(): string;

}
