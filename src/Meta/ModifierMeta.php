<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\ObjectMapper\Modifiers\Modifier;

final class ModifierMeta
{

	/** @var class-string<Modifier> */
	private string $type;

	/** @var array<mixed> */
	private array $args;

	private function __construct()
	{
	}

	/**
	 * @param class-string<Modifier> $type
	 * @param array<mixed> $args
	 */
	public static function from(string $type, array $args): self
	{
		$self = new self();
		$self->type = $type;
		$self->args = $args;

		return $self;
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
