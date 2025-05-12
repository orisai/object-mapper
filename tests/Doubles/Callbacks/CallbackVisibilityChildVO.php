<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\ObjectMapper\Callbacks\AfterValidation;
use Orisai\ObjectMapper\Rules\StringValue;

final class CallbackVisibilityChildVO extends CallbackVisibilityParentVO
{

	/**
	 * @AfterValidation("publicChildCb")
	 * @AfterValidation("protectedChildCb")
	 * @AfterValidation("privateChildCb")
	 */
	public string $public;

	/**
	 * @AfterValidation("publicChildCb")
	 * @AfterValidation("protectedChildCb")
	 * @AfterValidation("privateChildCb")
	 */
	protected string $protected;

	/**
	 * @StringValue()
	 * @AfterValidation("publicChildCb")
	 * @AfterValidation("protectedChildCb")
	 * @AfterValidation("privateChildCb")
	 */
	private string $privateChild;

	public function __construct(
		string $public,
		string $protected,
		string $privateParent,
		string $privateChild
	)
	{
		parent::__construct($public, $protected, $privateParent);
		$this->privateChild = $privateChild;
	}

	public function publicChildCb(string $value): string
	{
		return $value . '-pubCcCb';
	}

	protected function protectedChildCb(string $value): string
	{
		return $value . '-proCcCb';
	}

	private function privateChildCb(string $value): string
	{
		return $value . '-priCcCb';
	}

	public function getPrivateChild(): string
	{
		return $this->privateChild;
	}

}
