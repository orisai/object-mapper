<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;

/**
 * @internal
 */
final class ValueEnumArgs implements Args
{

	/** @var array<int|string, mixed> */
	public array $values;

	public bool $useKeys;

	/**
	 * @param array<int|string, mixed> $values
	 */
	public function __construct(array $values, bool $useKeys)
	{
		$this->values = $values;
		$this->useKeys = $useKeys;
	}

}
