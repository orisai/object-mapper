<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Definition;

use Orisai\ObjectMapper\Callbacks\BeforeValidationCallback;
use Orisai\ObjectMapper\Callbacks\CallbackDefinition;

final class AttributeLessCallbackDefinition extends CallbackDefinition
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
