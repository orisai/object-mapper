<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Modifiers;

use Orisai\ObjectMapper\Annotation\BaseAnnotation;
use Orisai\ObjectMapper\Meta\MetaSource;
use Orisai\ObjectMapper\Modifiers\Modifier;

interface ModifierAnnotation extends BaseAnnotation
{

	public const ANNOTATION_TYPE = MetaSource::TYPE_MODIFIERS;

	/**
	 * @phpstan-return class-string<Modifier>
	 */
	public function getType(): string;

}
