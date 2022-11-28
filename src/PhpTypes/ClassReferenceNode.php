<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\PhpTypes;

use function array_key_last;

final class ClassReferenceNode implements Node
{

	/** @var class-string */
	private string $class;

	/** @var array<int|string, Node> */
	private array $structure;

	/**
	 * @param class-string            $class
	 * @param array<int|string, Node> $structure
	 */
	public function __construct(string $class, array $structure)
	{
		$this->class = $class;
		$this->structure = $structure;
	}

	public function getArrayShape(): string
	{
		$inline = '';
		$lastKey = array_key_last($this->structure);
		foreach ($this->structure as $field => $node) {
			$inline .=
				$field
				. ': '
				. ((string) $node);

			if ($field !== $lastKey) {
				$inline .= ', ';
			}
		}

		return "array{{$inline}}";
	}

	public function __toString(): string
	{
		return $this->class;
	}

}
