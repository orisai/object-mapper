<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\PhpTypes;

use function array_key_last;

final class CompoundNode implements Node
{

	/** @var array<int, Node> */
	private array $nodes;

	private string $operator;

	/**
	 * @param array<int, Node> $nodes
	 */
	private function __construct(array $nodes, string $operator)
	{
		$this->nodes = $nodes;
		$this->operator = $operator;
	}

	/**
	 * @param array<int, Node> $nodes
	 */
	public static function createAndType(array $nodes): self
	{
		return new self($nodes, '&');
	}

	/**
	 * @param array<int, Node> $nodes
	 */
	public static function createOrType(array $nodes): self
	{
		return new self($nodes, '|');
	}

	public function __toString(): string
	{
		$string = '';
		$lastKey = array_key_last($this->nodes);
		foreach ($this->nodes as $key => $node) {
			$string .= $node;

			if ($key !== $lastKey) {
				$string .= $this->operator;
			}
		}

		return "($string)";
	}

}
