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
	private string $parentPrivate;

	/**
	 * @StringValue()
	 * @After("afterProperty")
	 * @After("afterPropertyStatic")
	 */
	protected string $parentProtected;

	/**
	 * @StringValue()
	 * @After("afterProperty")
	 * @After("afterPropertyStatic")
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
