<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Attributes\Modifiers\FieldName;
use Orisai\ObjectMapper\MappedObject;

final class FieldNamesVO implements MappedObject
{

	/** @StringValue() */
	public string $original;

	/**
	 * @StringValue()
	 * @FieldName("field")
	 */
	public string $property;

	/**
	 * @StringValue()
	 * @FieldName(123)
	 */
	public string $integer;

	/**
	 * @StringValue()
	 * @FieldName("swap2")
	 */
	public string $swap1;

	/**
	 * @StringValue()
	 * @FieldName("swap1")
	 */
	public string $swap2;

}
