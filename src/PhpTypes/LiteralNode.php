<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\PhpTypes;

use function is_bool;
use function is_float;
use function is_int;
use function var_export;

final class LiteralNode implements Node
{

	/** @var int|float|string|bool|null */
	private $value;

	/**
	 * @param int|float|string|bool|null $value
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}

	public function __toString(): string
	{
		if (is_bool($this->value)) {
			return $this->value ? 'true' : 'false';
		}

		if ($this->value === null) {
			return 'null';
		}

		if (is_int($this->value) || is_float($this->value)) {
			return var_export($this->value, true);
		}

		return "'{$this->value}'";
	}

}
