<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Meta;

use Orisai\ObjectMapper\MappedObject;
use Tests\Orisai\ObjectMapper\Doubles\Definition\UnsupportedDefinition;

final class UnsupportedPropertyDefinitionVO implements MappedObject
{

	/** @UnsupportedDefinition() */
	public string $field;

}
