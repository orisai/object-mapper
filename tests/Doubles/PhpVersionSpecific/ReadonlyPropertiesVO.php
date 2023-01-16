<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific;

use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class ReadonlyPropertiesVO implements MappedObject
{

	#[StringValue]
	public readonly string $readonly;

}
