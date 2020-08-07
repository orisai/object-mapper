<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\ObjectMapper\Modifiers\Modifier;

abstract class SharedMeta
{

	/** @var array<mixed> */
	private array $callbacks;

	/** @var array<CallbackMeta>|null */
	private ?array $instCallbacks = null;

	/** @var array<string, array<mixed>> */
	private array $docs;

	/** @var array<DocMeta>|null */
	private ?array $instDocs = null;

	/**
	 * @var array<string, array<mixed>>
	 * @phpstan-var array<class-string<Modifier>, array<mixed>>
	 */
	private array $modifiers;

	/** @var array<ModifierMeta>|null */
	private ?array $instModifiers = null;

	final private function __construct()
	{
	}

	/**
	 * @param array<mixed> $meta
	 * @return static
	 */
	public static function fromArray(array $meta): self
	{
		$self = new static();
		$self->callbacks = $meta[MetaSource::TYPE_CALLBACKS] ?? [];
		$self->docs = $meta[MetaSource::TYPE_DOCS] ?? [];
		$self->modifiers = $meta[MetaSource::TYPE_MODIFIERS] ?? [];

		return $self;
	}

	/**
	 * @return array<CallbackMeta>
	 */
	public function getCallbacks(): array
	{
		if ($this->instCallbacks !== null) {
			return $this->instCallbacks;
		}

		$processed = [];

		foreach ($this->callbacks as $callback) {
			$processed[] = CallbackMeta::fromArray($callback);
		}

		return $this->instCallbacks = $processed;
	}

	/**
	 * @return array<DocMeta>
	 */
	public function getDocs(): array
	{
		if ($this->instDocs !== null) {
			return $this->instDocs;
		}

		$processed = [];

		foreach ($this->docs as $name => $args) {
			$processed[] = DocMeta::from($name, $args);
		}

		return $this->instDocs = $processed;
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
	 * @phpstan-param class-string<Modifier> $type
	 */
	public function getModifier(string $type): ?ModifierMeta
	{
		return $this->getModifiers()[$type] ?? null;
	}

}
