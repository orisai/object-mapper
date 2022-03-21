<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Modifiers\Modifier;

final class ModifierRuntimeMeta
{

	/** @var class-string<Modifier<Args>> */
	private string $type;

	private Args $args;

	/**
	 * @param class-string<Modifier<Args>> $type
	 */
	public function __construct(string $type, Args $args)
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

	public function getArgs(): Args
	{
		return $this->args;
	}

}
