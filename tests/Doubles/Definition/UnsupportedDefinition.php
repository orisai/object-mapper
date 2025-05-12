<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Definition;

use Attribute;
use Orisai\ObjectMapper\Callbacks\AfterValidationCallback;
use Orisai\ObjectMapper\Meta\MetaDefinition;

/**
 * @Annotation
 */
#[Attribute]
final class UnsupportedDefinition implements MetaDefinition
{

	public function getScope(): string
	{
		return $this->getHandler();
	}

	public function getHandler(): string
	{
		return AfterValidationCallback::class;
	}

	public function getArgs(): array
	{
		return [];
	}

}
