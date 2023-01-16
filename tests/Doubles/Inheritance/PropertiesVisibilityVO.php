<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Inheritance;

use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class PropertiesVisibilityVO implements MappedObject
{

	/** @StringValue() */
	public string $public;

	/** @StringValue() */
	protected string $protected;

	/** @StringValue() */
	private string $private;

	public function getProtected(): string
	{
		return $this->protected;
	}

	public function getPrivate(): string
	{
		return $this->private;
	}

}
