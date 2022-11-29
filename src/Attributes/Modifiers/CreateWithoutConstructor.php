<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Modifiers;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Orisai\ObjectMapper\Modifiers\CreateWithoutConstructorModifier;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class CreateWithoutConstructor implements ModifierAttribute
{

	public function getType(): string
	{
		return CreateWithoutConstructorModifier::class;
	}

	public function getArgs(): array
	{
		return [];
	}

}
