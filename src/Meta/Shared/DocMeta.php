<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Shared;

use Orisai\ObjectMapper\Docs\Doc;

final class DocMeta
{

	/** @var class-string<Doc> */
	private string $name;

	/** @var array<mixed> */
	private array $args;

	/**
	 * @param class-string<Doc> $name
	 * @param array<mixed>      $args
	 */
	public function __construct(string $name, array $args)
	{
		$this->name = $name;
		$this->args = $args;
	}

	/**
	 * @return class-string<Doc>
	 */
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
