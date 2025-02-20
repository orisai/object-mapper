<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance;

use Orisai\ObjectMapper\Callbacks\AfterValidation;
use Orisai\ObjectMapper\Rules\StringValue;

final class ChildVO extends ParentVO
{

	use ChildTraitVO;

	/**
	 * @StringValue()
	 * @AfterValidation("afterProperty")
	 * @AfterValidation("afterPropertyStatic")
	 */
	private string $childPrivate;

	/**
	 * @StringValue()
	 * @AfterValidation("afterProperty")
	 * @AfterValidation("afterPropertyStatic")
	 */
	protected string $childProtected;

	/**
	 * @StringValue()
	 * @AfterValidation("afterProperty")
	 * @AfterValidation("afterPropertyStatic")
	 */
	public string $childPublic;

	private function afterProperty(string $value): string
	{
		return "$value-child";
	}

	private function afterPropertyStatic(string $value): string
	{
		return "$value-childStatic";
	}

	public function getChildPrivate(): string
	{
		return $this->childPrivate;
	}

	public function getChildProtected(): string
	{
		return $this->childProtected;
	}

}
