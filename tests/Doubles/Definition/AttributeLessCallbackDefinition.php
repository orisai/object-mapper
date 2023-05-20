<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Definition;

use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Callbacks\CallbackDefinition;

/**
 * @implements CallbackDefinition<BeforeCallback>
 */
final class AttributeLessCallbackDefinition implements CallbackDefinition
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
