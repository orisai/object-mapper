<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Definition;

use Orisai\ObjectMapper\Callbacks\BeforeValidationCallback;
use Orisai\ObjectMapper\Callbacks\CallbackDefinition;

final class AttributeLessCallbackDefinition implements CallbackDefinition
{

	public function getType(): string
	{
		return BeforeValidationCallback::class;
	}

	public function getArgs(): array
	{
		return [];
	}

}
