<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Definition;

use Attribute;
use Orisai\ObjectMapper\Callbacks\BeforeValidationCallback;
use Orisai\ObjectMapper\Callbacks\CallbackDefinition;

#[Attribute(Attribute::TARGET_ALL)]
final class WithExtraTargetCallbackAttributeDefinition extends CallbackDefinition
{

	public function getScope(): string
	{
		return $this->getHandler();
	}

	public function getHandler(): string
	{
		return BeforeValidationCallback::class;
	}

	public function getArgs(): array
	{
		return [];
	}

}
