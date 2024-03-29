<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance;

use Orisai\ObjectMapper\Callbacks\After;
use Orisai\ObjectMapper\Rules\StringValue;

trait ChildTraitVO
{

	/**
	 * @StringValue()
	 * @After("afterTraitProperty")
	 * @After("afterTraitPropertyStatic")
	 */
	private string $childTraitPrivate;

	/**
	 * @StringValue()
	 * @After("afterTraitProperty")
	 * @After("afterTraitPropertyStatic")
	 */
	protected string $childTraitProtected;

	/**
	 * @StringValue()
	 * @After("afterTraitProperty")
	 * @After("afterTraitPropertyStatic")
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
