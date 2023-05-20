<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Definition;

use Orisai\ObjectMapper\Modifiers\DefaultValueModifier;
use Orisai\ObjectMapper\Modifiers\ModifierDefinition;

/**
 * @implements ModifierDefinition<DefaultValueModifier>
 */
final class AttributeLessModifierDefinition implements ModifierDefinition
{

	public function getType(): string
	{
		return DefaultValueModifier::class;
	}

	public function getArgs(): array
	{
		return [];
	}

}
