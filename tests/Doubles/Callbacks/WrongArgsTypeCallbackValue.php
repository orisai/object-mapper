<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Orisai\ObjectMapper\Callbacks\CallbackDefinition;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class WrongArgsTypeCallbackValue extends CallbackDefinition
{

	public function getScope(): string
	{
		return $this->getHandler();
	}

	public function getHandler(): string
	{
		return WrongArgsTypeCallback::class;
	}

	public function getArgs(): array
	{
		return [];
	}

}
