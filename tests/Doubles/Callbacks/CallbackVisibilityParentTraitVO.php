<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\ObjectMapper\Callbacks\AfterValidation;
use Orisai\ObjectMapper\Rules\StringValue;

trait CallbackVisibilityParentTraitVO
{

	/**
	 * @StringValue()
	 * @AfterValidation("publicTraitCb")
	 * @AfterValidation("protectedTraitCb")
	 * @AfterValidation("privateTraitCb")
	 */
	public string $public;

	/**
	 * @StringValue()
	 * @AfterValidation("publicTraitCb")
	 * @AfterValidation("protectedTraitCb")
	 * @AfterValidation("privateTraitCb")
	 */
	protected string $protected;

	/**
	 * @StringValue()
	 * @AfterValidation("publicTraitCb")
	 * @AfterValidation("protectedTraitCb")
	 * @AfterValidation("privateTraitCb")
	 */
	private string $privateParent;

	public function publicTraitCb(string $value): string
	{
		return $value . '-pubPtCb';
	}

	protected function protectedTraitCb(string $value): string
	{
		return $value . '-proPtCb';
	}

	private function privateTraitCb(string $value): string
	{
		return $value . '-priPtCb';
	}

	public function getProtected(): string
	{
		return $this->protected;
	}

	public function getPrivateParent(): string
	{
		return $this->privateParent;
	}

}
