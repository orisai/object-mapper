<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Callbacks\Callback;
use Orisai\ObjectMapper\Meta\Shared\DocMeta;

/**
 * @internal
 */
abstract class NodeRuntimeMeta
{

	/** @var array<class-string<Callback<Args>>, array<int, CallbackRuntimeMeta<Args>>> */
	private array $callbacks;

	/** @var array<string, DocMeta> */
	private array $docs;

	/**
	 * @template T_ARGS of Args
	 * @param array<class-string<Callback<T_ARGS>>, array<int, CallbackRuntimeMeta<T_ARGS>>> $callbacks
	 * @param array<string, DocMeta> $docs
	 */
	public function __construct(array $callbacks, array $docs)
	{
		$this->callbacks = $callbacks;
		$this->docs = $docs;
	}

	public function hasAnyCallbacks(): bool
	{
		return $this->callbacks !== [];
	}

	/**
	 * @return array<class-string<Callback<Args>>, array<int, CallbackRuntimeMeta<Args>>>
	 */
	public function getCallbacks(): array
	{
		return $this->callbacks;
	}

	/**
	 * @return array<int, CallbackRuntimeMeta<Args>>
	 */
	public function getCallbacksByType(string $type): array
	{
		return $this->callbacks[$type] ?? [];
	}

	/**
	 * @return array<string, DocMeta>
	 */
	public function getDocs(): array
	{
		return $this->docs;
	}

	/**
	 * @return array<mixed>
	 */
	public function __serialize(): array
	{
		return [
			'callbacks' => $this->callbacks,
			'docs' => $this->docs,
		];
	}

	/**
	 * @param array<mixed> $data
	 */
	public function __unserialize(array $data): void
	{
		$this->callbacks = $data['callbacks'];
		$this->docs = $data['docs'];
	}

}
