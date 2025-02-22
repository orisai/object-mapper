<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Callbacks\Callback;

/**
 * @phpstan-type T_SERIALIZED array{type: class-string<Callback<T>>, args: T}
 *
 * @template-covariant T of Args
 *
 * @readonly
 */
final class CallbackRuntimeMeta
{

	/** @var class-string<Callback<T>> */
	public string $type;

	/** @var T */
	public Args $args;

	/**
	 * @param class-string<Callback<T>> $type
	 * @param T $args
	 */
	public function __construct(string $type, Args $args)
	{
		$this->type = $type;
		$this->args = $args;
	}

	/**
	 * @return T_SERIALIZED
	 */
	public function __serialize(): array
	{
		return [
			'type' => $this->type,
			'args' => $this->args,
		];
	}

	/**
	 * @param T_SERIALIZED $data
	 */
	public function __unserialize(array $data): void
	{
		$this->type = $data['type'];
		$this->args = $data['args'];
	}

}
