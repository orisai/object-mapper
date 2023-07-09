<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Definition;

use Attribute;
use Orisai\ObjectMapper\Meta\MetaDefinition;
use stdClass;

/**
 * @Annotation
 */
#[Attribute]
final class UnsupportedDefinition implements MetaDefinition
{

	public function getType(): string
	{
		return stdClass::class;
	}

	public function getArgs(): array
	{
		return [];
	}

}
