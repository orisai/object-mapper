<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

final class DocMeta
{

	private string $name;

	/** @var array<mixed> */
	private array $args;

	private function __construct()
	{
	}

	/**
	 * @param array<mixed> $args
	 */
	public static function from(string $name, array $args): self
	{
		$self = new self();
		$self->name = $name;
		$self->args = $args;

		return $self;
	}

	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return $this->args;
	}

}
