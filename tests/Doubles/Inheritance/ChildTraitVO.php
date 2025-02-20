<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance;

use Orisai\ObjectMapper\Callbacks\AfterValidation;
use Orisai\ObjectMapper\Rules\StringValue;

trait ChildTraitVO
{

	/**
	 * @StringValue()
	 * @AfterValidation("afterTraitProperty")
	 * @AfterValidation("afterTraitPropertyStatic")
	 */
	private string $childTraitPrivate;

	/**
	 * @StringValue()
	 * @AfterValidation("afterTraitProperty")
	 * @AfterValidation("afterTraitPropertyStatic")
	 */
	protected string $childTraitProtected;

	/**
	 * @StringValue()
	 * @AfterValidation("afterTraitProperty")
	 * @AfterValidation("afterTraitPropertyStatic")
	 */
	public string $childTraitPublic;

	private function afterTraitProperty(string $value): string
	{
		return "$value-childTrait";
	}

	private function afterTraitPropertyStatic(string $value): string
	{
		return "$value-childTraitStatic";
	}

	public function getChildTraitPrivate(): string
	{
		return $this->childTraitPrivate;
	}

	public function getChildTraitProtected(): string
	{
		return $this->childTraitProtected;
	}

}
