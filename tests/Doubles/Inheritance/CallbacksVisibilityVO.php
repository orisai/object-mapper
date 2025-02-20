<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance;

use Orisai\ObjectMapper\Callbacks\AfterValidation;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

final class CallbacksVisibilityVO implements MappedObject
{

	/**
	 * @StringValue()
	 * @AfterValidation("afterPublic")
	 */
	public string $public;

	/**
	 * @StringValue()
	 * @AfterValidation("afterProtected")
	 */
	public string $protected;

	/**
	 * @StringValue()
	 * @AfterValidation("afterPrivate")
	 */
	public string $private;

	/**
	 * @StringValue()
	 * @AfterValidation("afterPublicStatic")
	 */
	public string $publicStatic;

	/**
	 * @StringValue()
	 * @AfterValidation("afterProtectedStatic")
	 */
	public string $protectedStatic;

	/**
	 * @StringValue()
	 * @AfterValidation("afterPrivateStatic")
	 */
	public string $privateStatic;

	public function __construct(
		string $public,
		string $protected,
		string $private,
		string $publicStatic,
		string $protectedStatic,
		string $privateStatic
	)
	{
		$this->public = $public;
		$this->protected = $protected;
		$this->private = $private;
		$this->publicStatic = $publicStatic;
		$this->protectedStatic = $protectedStatic;
		$this->privateStatic = $privateStatic;
	}

	public function afterPublic(string $data): string
	{
		return "$data-public";
	}

	protected function afterProtected(string $data): string
	{
		return "$data-protected";
	}

	private function afterPrivate(string $data): string
	{
		return "$data-private";
	}

	public static function afterPublicStatic(string $data): string
	{
		return "$data-public-static";
	}

	protected static function afterProtectedStatic(string $data): string
	{
		return "$data-protected-static";
	}

	private static function afterPrivateStatic(string $data): string
	{
		return "$data-private-static";
	}

}
