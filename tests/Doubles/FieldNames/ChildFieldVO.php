<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\FieldNames;

use Orisai\ObjectMapper\Modifiers\FieldName;
use Orisai\ObjectMapper\Rules\StringValue;

final class ChildFieldVO extends ParentFieldVO
{

	/**
	 * @FieldName("renamedProperty")
	 * @StringValue()
	 */
	private string $property;

	public function getChildProperty(): string
	{
		return $this->property;
	}

}
