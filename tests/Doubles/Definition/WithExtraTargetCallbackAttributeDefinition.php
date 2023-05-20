<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Definition;

use Attribute;
use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Callbacks\CallbackDefinition;

#[Attribute(Attribute::TARGET_ALL)]
final class WithExtraTargetCallbackAttributeDefinition implements CallbackDefinition
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
