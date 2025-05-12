<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Definition;

use Orisai\ObjectMapper\Modifiers\DefaultValueModifier;
use Orisai\ObjectMapper\Modifiers\ModifierDefinition;

final class AttributeLessModifierDefinition implements ModifierDefinition
{

	public function getScope(): string
	{
		return $this->getHandler();
	}

	public function getHandler(): string
	{
		return DefaultValueModifier::class;
	}

	public function getArgs(): array
	{
		return [];
	}

}
