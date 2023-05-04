<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\FieldNames;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\FieldName;
use Orisai\ObjectMapper\Rules\StringValue;

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
