<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Modifiers;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * @implements ModifierDefinition<CreateWithoutConstructorModifier>
 *
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class CreateWithoutConstructor implements ModifierDefinition
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
