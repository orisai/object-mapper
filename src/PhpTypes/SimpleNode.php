<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\PhpTypes;

final class SimpleNode implements Node
{

	/** @var non-empty-string */
	private string $value;

	/**
	 * @param non-empty-string $value
	 */
	public function __construct(string $value)
	{
		$this->value = $value;
	}

	public function __toString(): string
	{
		return $this->value;
	}

}
