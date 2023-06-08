<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Shared\DocMeta;

/**
 * @internal
 */
abstract class NodeRuntimeMeta
{

	/** @var array<int, CallbackRuntimeMeta<Args>> */
	private array $callbacks;

	/** @var array<string, DocMeta> */
	private array $docs;

	/**
	 * @param array<int, CallbackRuntimeMeta<Args>> $callbacks
	 * @param array<string, DocMeta>                $docs
	 */
	public function __construct(array $callbacks, array $docs)
	{
		$this->callbacks = $callbacks;
		$this->docs = $docs;
	}

	/**
	 * @return array<int, CallbackRuntimeMeta<Args>>
	 */
	public function getCallbacks(): array
	{
		return $this->callbacks;
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
