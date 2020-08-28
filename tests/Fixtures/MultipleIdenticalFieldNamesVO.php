<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\ObjectMapper\Annotation\Expect\StringValue;
use Orisai\ObjectMapper\Annotation\Modifiers\FieldName;
use Orisai\ObjectMapper\ValueObject;

final class MultipleIdenticalFieldNamesVO extends ValueObject
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
