<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Modifiers\Modifier;

final class ModifierCompileMeta
{

	/** @var class-string<Modifier<Args>> */
	private string $type;

	/** @var array<mixed> */
	private array $args;

	/**
	 * @param class-string<Modifier<Args>> $type
	 * @param array<mixed>                 $args
	 */
	public function __construct(string $type, array $args)
	{
		$this->type = $type;
		$this->args = $args;
	}

	/**
	 * @return class-string<Modifier<Args>>
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
