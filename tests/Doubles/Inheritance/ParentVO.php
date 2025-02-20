<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance;

use Orisai\ObjectMapper\Callbacks\AfterValidation;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

abstract class ParentVO implements MappedObject
{

	/**
	 * @StringValue()
	 * @AfterValidation("afterProperty")
	 * @AfterValidation("afterPropertyStatic")
	 */
	private string $parentPrivate;

	/**
	 * @StringValue()
	 * @AfterValidation("afterProperty")
	 * @AfterValidation("afterPropertyStatic")
	 */
	protected string $parentProtected;

	/**
	 * @StringValue()
	 * @AfterValidation("afterProperty")
	 * @AfterValidation("afterPropertyStatic")
	 */
	public string $parentPublic;

	private function afterProperty(string $value): string
	{
		return "$value-parent";
	}

	private function afterPropertyStatic(string $value): string
	{
		return "$value-parentStatic";
	}

	public function getParentPrivate(): string
	{
		return $this->parentPrivate;
	}

	public function getParentProtected(): string
	{
		return $this->parentProtected;
	}

}
