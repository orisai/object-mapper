<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;

/**
 * @internal
 */
final class FloatArgs implements Args
{

	public ?float $min;

	public ?float $max;

	public bool $unsigned;

	public bool $castNumericString;

	public function __construct(
		?float $min = null,
		?float $max = null,
		bool $unsigned = true,
		bool $castNumericString = false
	)
	{
		$this->min = $min;
		$this->max = $max;
		$this->unsigned = $unsigned;
		$this->castNumericString = $castNumericString;
	}

}
