<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Types;

use Orisai\ObjectMapper\Exception\WithTypeAndValue;

final class KeyValueErrorPair
{

	private ?WithTypeAndValue $key;

	private ?WithTypeAndValue $value;

	/**
	 * @internal
	 */
	public function __construct(?WithTypeAndValue $key, ?WithTypeAndValue $value)
	{
		$this->key = $key;
		$this->value = $value;
	}

	public function getKey(): ?WithTypeAndValue
	{
		return $this->key;
	}

	public function getValue(): ?WithTypeAndValue
	{
		return $this->value;
	}

}
