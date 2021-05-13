<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Modifiers;

use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 * @property-write int|string $name
 */
final class FieldName implements ModifierAnnotation
{

	use AutoMappedAnnotation;

	protected function getMainProperty(): string
	{
		return 'name';
	}

	public function getType(): string
	{
		return FieldNameModifier::class;
	}

}
