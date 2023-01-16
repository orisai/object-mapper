<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance;

use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;

trait ChildTraitVO
{

	/**
	 * @StringValue()
	 * @After("afterTraitProperty")
	 * @After("afterTraitPropertyStatic")
	 */
	private string $childTrait;

	private function afterTraitProperty(string $value): string
	{
		return "$value-childTrait";
	}

	private function afterTraitPropertyStatic(string $value): string
	{
		return "$value-childTraitStatic";
	}

	public function getChildTraitProperty(): string
	{
		return $this->childTrait;
	}

}
