<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Attributes\Callbacks\After;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class CallbacksVisibilityVO implements MappedObject
{

	/**
	 * @StringValue()
	 * @After("afterPublic")
	 */
	public string $public;

	/**
	 * @StringValue()
	 * @After("afterProtected")
	 */
	public string $protected;

	/**
	 * @StringValue()
	 * @After("afterPrivate")
	 */
	public string $private;

	/**
	 * @StringValue()
	 * @After("afterPublicStatic")
	 */
	public string $publicStatic;

	/**
	 * @StringValue()
	 * @After("afterProtectedStatic")
	 */
	public string $protectedStatic;

	/**
	 * @StringValue()
	 * @After("afterPrivateStatic")
	 */
	public string $privateStatic;

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
