<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\StringValue;

final class PropertiesVisibilityVO implements MappedObject
{

	/** @StringValue() */
	public string $public;

	/** @StringValue() */
	protected string $protected;

	/** @StringValue() */
	private string $private;

	public function __construct(string $public, string $protected, string $private)
	{
		$this->public = $public;
		$this->protected = $protected;
		$this->private = $private;
	}

	public function getProtected(): string
	{
		return $this->protected;
	}

	public function getPrivate(): string
	{
		return $this->private;
	}

}
