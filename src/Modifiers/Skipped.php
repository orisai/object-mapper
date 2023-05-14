<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Modifiers;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @implements ModifierDefinition<SkippedModifier>
 *
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Skipped implements ModifierDefinition
{

	public function getType(): string
	{
		return SkippedModifier::class;
	}

	public function getArgs(): array
	{
		return [];
	}

}
