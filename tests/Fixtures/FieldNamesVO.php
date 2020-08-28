<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\ObjectMapper\Annotation\Expect\StringValue;
use Orisai\ObjectMapper\Annotation\Modifiers\FieldName;
use Orisai\ObjectMapper\ValueObject;

final class FieldNamesVO extends ValueObject
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
