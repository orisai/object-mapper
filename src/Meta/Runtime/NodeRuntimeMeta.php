<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Shared\DocMeta;
use Orisai\ObjectMapper\Modifiers\Modifier;

/**
 * @internal
 */
abstract class NodeRuntimeMeta
{

	/** @var array<int, CallbackRuntimeMeta<Args>> */
	private array $callbacks;

	/** @var array<string, DocMeta> */
	private array $docs;

	/** @var array<class-string<Modifier<Args>>, ModifierRuntimeMeta<Args>> */
	private array $modifiers;

	/**
	 * @param array<int, CallbackRuntimeMeta<Args>>                          $callbacks
	 * @param array<string, DocMeta>                                         $docs
	 * @param array<class-string<Modifier<Args>>, ModifierRuntimeMeta<Args>> $modifiers
	 */
	public function __construct(array $callbacks, array $docs, array $modifiers)
	{
		$this->callbacks = $callbacks;
		$this->docs = $docs;
		$this->modifiers = $modifiers;
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
	 * @return array<class-string<Modifier<Args>>, ModifierRuntimeMeta<Args>>
	 */
	public function getModifiers(): array
	{
		return $this->modifiers;
	}

	/**
	 * @template T of Args
	 * @param class-string<Modifier<T>> $type
	 * @return ModifierRuntimeMeta<T>|null
	 */
	public function getModifier(string $type): ?ModifierRuntimeMeta
	{
		return $this->getModifiers()[$type] ?? null;
	}

	/**
	 * @return array<mixed>
	 */
	public function __serialize(): array
	{
		return [
			'callbacks' => $this->callbacks,
			'docs' => $this->docs,
			'modifiers' => $this->modifiers,
		];
	}

	/**
	 * @param array<mixed> $data
	 */
	public function __unserialize(array $data): void
	{
		$this->callbacks = $data['callbacks'];
		$this->docs = $data['docs'];
		$this->modifiers = $data['modifiers'];
	}

}
