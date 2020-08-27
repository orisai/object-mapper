<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Modifiers;

use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Modifiers\SkippedModifier;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Skipped implements ModifierAnnotation
{

	use AutoMappedAnnotation;

	public function getType(): string
	{
		return SkippedModifier::class;
	}

}
