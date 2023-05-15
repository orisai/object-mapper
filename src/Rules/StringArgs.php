<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;

/**
 * @internal
 */
final class StringArgs implements Args
{

	public ?string $pattern;

	public bool $notEmpty;

	public ?int $minLength;

	public ?int $maxLength;

	public function __construct(
		?string $pattern,
		bool $notEmpty,
		?int $minLength,
		?int $maxLength
	)
	{
		$this->pattern = $pattern;
		$this->notEmpty = $notEmpty;
		$this->minLength = $minLength;
		$this->maxLength = $maxLength;
	}

}
