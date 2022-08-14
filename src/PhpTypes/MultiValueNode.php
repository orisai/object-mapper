<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\PhpTypes;

final class MultiValueNode implements Node
{

	private string $name;

	private ?Node $key;

	private Node $item;

	public function __construct(string $name, ?Node $key, Node $item)
	{
		$this->name = $name;
		$this->key = $key;
		$this->item = $item;
	}

	public function __toString(): string
	{
		return $this->name
			. '<'
			. ($this->key !== null ? "$this->key, " : '')
			. ((string) $this->item)
			. '>';
	}

}
