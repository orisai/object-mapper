<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use Orisai\ObjectMapper\Meta\CallbackMeta;
use Orisai\ObjectMapper\Meta\DocMeta;
use Orisai\ObjectMapper\Meta\ModifierMeta;

/**
 * @internal
 */
abstract class SharedNodeCompileMeta
{

	/** @var array<int, CallbackMeta> */
	private array $callbacks;

	/** @var array<int, DocMeta> */
	private array $docs;

	/** @var array<int, ModifierMeta> */
	private array $modifiers;

	/**
	 * @param array<int, CallbackMeta> $callbacks
	 * @param array<int, DocMeta>      $docs
	 * @param array<int, ModifierMeta> $modifiers
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
	 * @return array<int, DocMeta>
	 */
	public function getDocs(): array
	{
		return $this->docs;
	}

	/**
	 * @return array<int, ModifierMeta>
	 */
	public function getModifiers(): array
	{
		return $this->modifiers;
	}

}
