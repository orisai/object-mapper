<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Modifiers\Modifier;

/**
 * @template-covariant T of Args
 */
final class ModifierRuntimeMeta
{

	/** @var class-string<Modifier<T>> */
	private string $type;

	/** @var T */
	private Args $args;

	/**
	 * @param class-string<Modifier<T>> $type
	 * @param T $args
	 */
	public function __construct(string $type, Args $args)
	{
		$this->type = $type;
		$this->args = $args;
	}

	/**
	 * @return class-string<Modifier<T>>
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return T
	 */
	public function getArgs(): Args
	{
		return $this->args;
	}

	/**
	 * @return array<mixed>
	 */
	public function __serialize(): array
	{
		return [
			'type' => $this->type,
			'args' => $this->args,
		];
	}

	/**
	 * @param array<mixed> $data
	 */
	public function __unserialize(array $data): void
	{
		$this->type = $data['type'];
		$this->args = $data['args'];
	}

}
