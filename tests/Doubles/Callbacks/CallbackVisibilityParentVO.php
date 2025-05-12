<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Callbacks;

use Orisai\ObjectMapper\Callbacks\AfterValidation;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

class CallbackVisibilityParentVO implements MappedObject
{

	//TODO - define trait and interface behavior
	//	- private property from trait and class is not merged
	//	- alternatively, traits properties could be overridden by class properties, but that still leaves above trait
	//		definitions and interface definitions
	//use CallbackVisibilityParentTraitVO;

	/**
	 * @StringValue()
	 * @AfterValidation("publicParentCb")
	 * @AfterValidation("protectedParentCb")
	 * @AfterValidation("privateParentCb")
	 */
	public string $public;

	/**
	 * @StringValue()
	 * @AfterValidation("publicParentCb")
	 * @AfterValidation("protectedParentCb")
	 * @AfterValidation("privateParentCb")
	 */
	protected string $protected;

	/**
	 * @StringValue()
	 * @AfterValidation("publicParentCb")
	 * @AfterValidation("protectedParentCb")
	 * @AfterValidation("privateParentCb")
	 */
	private string $privateParent;

	public function __construct(
		string $public,
		string $protected,
		string $privateParent
	)
	{
		$this->public = $public;
		$this->protected = $protected;
		$this->privateParent = $privateParent;
	}

	public function publicParentCb(string $value): string
	{
		return $value . '-puPcCb';
	}

	protected function protectedParentCb(string $value): string
	{
		return $value . '-proPcCb';
	}

	private function privateParentCb(string $value): string
	{
		return $value . '-priPcCb';
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
