<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\ObjectMapper\Modifiers\Modifier;

final class ModifierMeta
{

	/** @var class-string<Modifier> */
	private string $type;

	/** @var array<mixed> */
	private array $args;

	/**
	 * @param class-string<Modifier> $type
	 * @param array<mixed>                 $args
	 */
	public function __construct(string $type, array $args)
	{
		$this->type = $type;
		$this->args = $args;
	}

	/**
	 * @return class-string<Modifier>
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return $this->args;
	}

}
