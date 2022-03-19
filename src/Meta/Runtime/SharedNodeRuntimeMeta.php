<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Meta\CallbackMeta;
use Orisai\ObjectMapper\Meta\DocMeta;
use Orisai\ObjectMapper\Meta\ModifierMeta;
use Orisai\ObjectMapper\Modifiers\Modifier;

/**
 * @internal
 */
abstract class SharedNodeRuntimeMeta
{

	/** @var array<int, CallbackMeta> */
	private array $callbacks;

	/** @var array<string, DocMeta> */
	private array $docs;

	/** @var array<class-string<Modifier>, array<mixed>> */
	private array $modifiers;

	/** @var array<ModifierMeta>|null */
	private ?array $instModifiers = null;

	/**
	 * @param array<int, CallbackMeta>                    $callbacks
	 * @param array<string, DocMeta>                      $docs
	 * @param array<class-string<Modifier>, array<mixed>> $modifiers
	 */
	public function __construct(array $callbacks, array $docs, array $modifiers)
	{
		$this->callbacks = $callbacks;
		$this->docs = $docs;
		$this->modifiers = $modifiers;
	}

	/**
	 * @return array<int, CallbackMeta>
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
	 * @return array<ModifierMeta>
	 */
	public function getModifiers(): array
	{
		if ($this->instModifiers !== null) {
			return $this->instModifiers;
		}

		$processed = [];

		foreach ($this->modifiers as $type => $args) {
			$processed[$type] = ModifierMeta::from($type, $args);
		}

		return $this->instModifiers = $processed;
	}

	/**
	 * @param class-string<Modifier> $type
	 */
	public function getModifier(string $type): ?ModifierMeta
	{
		return $this->getModifiers()[$type] ?? null;
	}

}
