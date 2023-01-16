<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance;

use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;

final class ChildVO extends ParentVO
{

	/**
	 * @StringValue()
	 * @After("afterProperty")
	 * @After("afterPropertyStatic")
	 */
	private string $child;

	private function afterProperty(string $value): string
	{
		return "$value-child";
	}

	private function afterPropertyStatic(string $value): string
	{
		return "$value-childStatic";
	}

	public function getChildProperty(): string
	{
		return $this->child;
	}

}
