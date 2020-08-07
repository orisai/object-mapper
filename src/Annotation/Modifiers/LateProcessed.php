<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Modifiers;

use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Modifiers\LateProcessedModifier;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 * @property-write array<mixed> $context
 */
final class LateProcessed implements ModifierAnnotation
{

	use AutoMappedAnnotation;

	public function getType(): string
	{
		return LateProcessedModifier::class;
	}

}
