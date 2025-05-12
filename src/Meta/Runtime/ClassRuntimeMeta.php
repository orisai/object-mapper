<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Callbacks\AfterMappingCallback;
use Orisai\ObjectMapper\Callbacks\AfterValidationCallback;
use Orisai\ObjectMapper\Callbacks\BeforeValidationCallback;
use Orisai\ObjectMapper\Callbacks\Callback;
use Orisai\ObjectMapper\Modifiers\Modifier;

final class ClassRuntimeMeta implements NodeRuntimeMeta
{

	/** @var array<class-string<Callback<Args>>, list<CallbackRuntimeMeta<Args>>> */
	public array $callbacks;

	/** @var array<class-string<Modifier<Args>>, list<ModifierRuntimeMeta<Args>>> */
	public array $modifiers;

	/**
	 * @template T_ARGS of Args
	 * @param array<class-string<Callback<T_ARGS>>, list<CallbackRuntimeMeta<T_ARGS>>> $callbacks
	 * @param array<class-string<Modifier<T_ARGS>>, list<ModifierRuntimeMeta<T_ARGS>>> $modifiers
	 */
	public function __construct(array $callbacks, array $modifiers)
	{
		$this->callbacks = $callbacks;
		$this->modifiers = $modifiers;
	}

	/**
	 * @return list<CallbackRuntimeMeta<Args>>
	 */
	public function getCallbacksByType(string $type): array
	{
		return $this->callbacks[$type] ?? [];
	}

	public function getBeforeValidationCallbacks(): array
	{
		return $this->getCallbacksByType(BeforeValidationCallback::class);
	}

	public function getAfterValidationCallbacks(): array
	{
		return $this->getCallbacksByType(AfterValidationCallback::class);
	}

	/**
	 * @return list<CallbackRuntimeMeta<Args>>
	 */
	public function getAfterMappingCallbacks(): array
	{
		return $this->getCallbacksByType(AfterMappingCallback::class);
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
			'callbacks' => $this->callbacks,
			'modifiers' => $this->modifiers,
		];
	}

	/**
	 * @param array<mixed> $data
	 */
	public function __unserialize(array $data): void
	{
		$this->callbacks = $data['callbacks'];
		$this->modifiers = $data['modifiers'];
	}

}
