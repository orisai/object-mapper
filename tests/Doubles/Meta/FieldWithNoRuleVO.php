<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Meta;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\FieldName;

final class FieldWithNoRuleVO implements MappedObject
{

	/** @FieldName("foo") */
	public string $field;

}
