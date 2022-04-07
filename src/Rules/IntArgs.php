<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;

/**
 * @internal
 */
final class IntArgs implements Args
{

	public ?int $min;

	public ?int $max;

	public bool $unsigned;

	public bool $castNumericString;

	public function __construct(
		?int $min = null,
		?int $max = null,
		bool $unsigned = false,
		bool $castNumericString = false
	)
	{
		$this->min = $min;
		$this->max = $max;
		$this->unsigned = $unsigned;
		$this->castNumericString = $castNumericString;
	}

}
