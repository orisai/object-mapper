<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Rules\Rule;

/**
 * @template-covariant T of Args
 *
 * @readonly
 */
final class RuleRuntimeMeta
{

	/** @var class-string<Rule<T>> */
	public string $type;

	/** @var T */
	public Args $args;

	/**
	 * @param class-string<Rule<T>> $type
	 * @param T $args
	 */
	public function __construct(string $type, Args $args)
	{
		$this->type = $type;
		$this->args = $args;
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
