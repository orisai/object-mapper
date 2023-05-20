<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;

/**
 * @internal
 */
final class ArrayEnumArgs implements Args
{

	/** @var array<int|string, mixed> */
	public array $cases;

	public bool $useKeys;

	public bool $allowUnknown;

	/**
	 * @param array<int|string, mixed> $values
	 */
	public function __construct(array $values, bool $useKeys, bool $allowUnknown)
	{
		$this->cases = $values;
		$this->useKeys = $useKeys;
		$this->allowUnknown = $allowUnknown;
	}

}
