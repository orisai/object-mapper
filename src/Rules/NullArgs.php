<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;

/**
 * @internal
 */
final class NullArgs implements Args
{

	public bool $castEmptyString;

	public function __construct(bool $castEmptyString = false)
	{
		$this->castEmptyString = $castEmptyString;
	}

}
