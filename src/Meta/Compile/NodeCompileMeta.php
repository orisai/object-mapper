<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use Orisai\ObjectMapper\Meta\Shared\DocMeta;
use Orisai\ReflectionMeta\Structure\ClassStructure;

/**
 * @internal
 */
abstract class NodeCompileMeta
{

	/** @var list<CallbackCompileMeta> */
	private array $callbacks;

	/** @var list<DocMeta> */
	private array $docs;

	/** @var list<ModifierCompileMeta> */
	private array $modifiers;

	/**
	 * @param list<CallbackCompileMeta> $callbacks
	 * @param list<DocMeta> $docs
	 * @param list<ModifierCompileMeta> $modifiers
	 */
	public function __construct(array $callbacks, array $docs, array $modifiers)
	{
		$this->callbacks = $callbacks;
		$this->docs = $docs;
		$this->modifiers = $modifiers;
	}

	/**
	 * @return list<CallbackCompileMeta>
	 */
	public function getCallbacks(): array
	{
		return $this->callbacks;
	}

	/**
	 * @return list<DocMeta>
	 */
	public function getDocs(): array
	{
		return $this->docs;
	}

	/**
	 * @return list<ModifierCompileMeta>
	 */
	public function getModifiers(): array
	{
		return $this->modifiers;
	}

	abstract public function getClass(): ClassStructure;

}
