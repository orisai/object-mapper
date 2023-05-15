<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;

/**
 * @internal
 */
final class BoolArgs implements Args
{

	public bool $castBoolLike;

	public function __construct(bool $castBoolLike)
	{
		$this->castBoolLike = $castBoolLike;
	}

}
