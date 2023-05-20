<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Definition;

use Attribute;
use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Callbacks\CallbackDefinition;

/**
 * @implements CallbackDefinition<BeforeCallback>
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class WithMissingTargetCallbackAttributeDefinition implements CallbackDefinition
{

	public function getType(): string
	{
		return BeforeCallback::class;
	}

	public function getArgs(): array
	{
		return [];
	}

}
