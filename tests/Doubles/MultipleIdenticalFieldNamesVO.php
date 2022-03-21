<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Attributes\Modifiers\FieldName;
use Orisai\ObjectMapper\MappedObject;

final class MultipleIdenticalFieldNamesVO extends MappedObject
{

	/**
	 * @StringValue()
	 * @FieldName("field")
	 */
	public string $property1;

	/**
	 * @StringValue()
	 * @FieldName("field")
	 */
	public string $property2;

}
