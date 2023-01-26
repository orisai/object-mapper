<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance;

use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;

final class ChildVO extends ParentVO
{

	use ChildTraitVO;

	/**
	 * @StringValue()
	 * @After("afterProperty")
	 * @After("afterPropertyStatic")
	 */
	private string $childPrivate;

	/**
	 * @StringValue()
	 * @After("afterProperty")
	 * @After("afterPropertyStatic")
	 */
	protected string $childProtected;

	/**
	 * @StringValue()
	 * @After("afterProperty")
	 * @After("afterPropertyStatic")
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
