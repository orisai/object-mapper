<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\DocMeta;
use ReflectionClass;

/**
 * @internal
 */
abstract class NodeCompileMeta
{

	/** @var array<int, CallbackCompileMeta> */
	private array $callbacks;

	/** @var array<int, DocMeta> */
	private array $docs;

	/** @var array<int, ModifierCompileMeta> */
	private array $modifiers;

	/**
	 * @param array<int, CallbackCompileMeta> $callbacks
	 * @param array<int, DocMeta>             $docs
	 * @param array<int, ModifierCompileMeta> $modifiers
	 */
	public function __construct(array $callbacks, array $docs, array $modifiers)
	{
		$this->callbacks = $callbacks;
		$this->docs = $docs;
		$this->modifiers = $modifiers;
	}

	/**
	 * @return array<int, CallbackCompileMeta>
	 */
	public function getCallbacks(): array
	{
		return $this->callbacks;
	}

	/**
	 * @return array<int, DocMeta>
	 */
	public function getDocs(): array
	{
		return $this->docs;
	}

	/**
	 * @return array<int, ModifierCompileMeta>
	 */
	public function getModifiers(): array
	{
		return $this->modifiers;
	}

	/**
	 * @return ReflectionClass<MappedObject>
	 */
	abstract public function getClass(): ReflectionClass;

}
