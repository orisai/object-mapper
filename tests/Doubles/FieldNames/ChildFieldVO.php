<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\FieldNames;

use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Attributes\Modifiers\FieldName;

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
