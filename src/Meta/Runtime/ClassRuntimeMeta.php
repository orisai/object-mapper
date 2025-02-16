<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Modifiers\Modifier;

final class ClassRuntimeMeta extends NodeRuntimeMeta
{

	/** @var array<class-string<Modifier<Args>>, list<ModifierRuntimeMeta<Args>>> */
	public array $modifiers;

	/**
	 * @template T_ARGS of Args
	 * @param array<class-string<Modifier<T_ARGS>>, list<ModifierRuntimeMeta<T_ARGS>>> $modifiers
	 */
	public function __construct(array $callbacks, array $docs, array $modifiers)
	{
		parent::__construct($callbacks, $docs);
		$this->modifiers = $modifiers;
	}

	/**
	 * @template T of Args
	 * @param class-string<Modifier<T>> $type
	 * @return list<ModifierRuntimeMeta<T>>
	 */
	public function getModifier(string $type): array
	{
		return $this->modifiers[$type] ?? [];
	}

	/**
	 * @return array<mixed>
	 */
	public function __serialize(): array
	{
		return [
			'parent' => parent::__serialize(),
			'modifiers' => $this->modifiers,
		];
	}

	/**
	 * @param array<mixed> $data
	 */
	public function __unserialize(array $data): void
	{
		parent::__unserialize($data['parent']);
		$this->modifiers = $data['modifiers'];
	}

}
