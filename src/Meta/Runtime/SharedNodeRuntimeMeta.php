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

	/** @var array<class-string<Modifier<Args>>, Args> */
	private array $modifiers;

	/** @var array<class-string<Modifier<Args>>, ModifierRuntimeMeta>|null */
	private ?array $instModifiers = null;

	/**
	 * @param array<int, CallbackRuntimeMeta>           $callbacks
	 * @param array<string, DocMeta>                    $docs
	 * @param array<class-string<Modifier<Args>>, Args> $modifiers
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
	 * @return array<class-string<Modifier<Args>>, ModifierRuntimeMeta>
	 */
	public function getModifiers(): array
	{
		if ($this->instModifiers !== null) {
			return $this->instModifiers;
		}

		$processed = [];

		foreach ($this->modifiers as $type => $args) {
			$processed[$type] = new ModifierRuntimeMeta($type, $args);
		}

		return $this->instModifiers = $processed;
	}

	/**
	 * @param class-string<Modifier> $type
	 */
	public function getModifier(string $type): ?ModifierRuntimeMeta
	{
		return $this->getModifiers()[$type] ?? null;
	}

}
