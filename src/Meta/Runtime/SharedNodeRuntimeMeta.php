<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\DocMeta;
use Orisai\ObjectMapper\Modifiers\Modifier;

/**
 * @internal
 */
abstract class SharedNodeRuntimeMeta
{

	/** @var array<int, CallbackRuntimeMeta> */
	private array $callbacks;

	/** @var array<string, DocMeta> */
	private array $docs;

	/** @var array<class-string<Modifier<Args>>, ModifierRuntimeMeta<Args>> */
	private array $modifiers;

	/**
	 * @param array<int, CallbackRuntimeMeta>                          $callbacks
	 * @param array<string, DocMeta>                                   $docs
	 * @param array<class-string<Modifier<Args>>, ModifierRuntimeMeta<Args>> $modifiers
	 */
	public function __construct(array $callbacks, array $docs, array $modifiers)
	{
		$this->callbacks = $callbacks;
		$this->docs = $docs;
		$this->modifiers = $modifiers;
	}

	/**
	 * @return array<int, CallbackRuntimeMeta>
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
	 * @param class-string<Modifier> $type
	 * @return ModifierRuntimeMeta<Args>|null
	 */
	public function getModifier(string $type): ?ModifierRuntimeMeta
	{
		return $this->getModifiers()[$type] ?? null;
	}

}
