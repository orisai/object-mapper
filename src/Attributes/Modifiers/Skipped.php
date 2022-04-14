<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Modifiers;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Orisai\ObjectMapper\Modifiers\SkippedModifier;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY"})
 */
final class Skipped implements ModifierAttribute
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
