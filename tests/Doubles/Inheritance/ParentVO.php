<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance;

use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

abstract class ParentVO implements MappedObject
{

	/**
	 * @StringValue()
	 * @After("afterProperty")
	 * @After("afterPropertyStatic")
	 */
	private string $parent;

	private function afterProperty(string $value): string
	{
		return "$value-parent";
	}

	private function afterPropertyStatic(string $value): string
	{
		return "$value-parentStatic";
	}

	public function getParentProperty(): string
	{
		return $this->parent;
	}

}
