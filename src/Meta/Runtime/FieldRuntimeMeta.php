<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;
use Orisai\ObjectMapper\Modifiers\Modifier;

final class FieldRuntimeMeta extends NodeRuntimeMeta
{

	/** @var RuleRuntimeMeta<Args> */
	public RuleRuntimeMeta $rule;

	public DefaultValueMeta $default;

	public PhpPropertyMeta $property;

	/** @var array<class-string<Modifier<Args>>, ModifierRuntimeMeta<Args>> */
	public array $modifiers;

	/**
	 * @template T_ARGS of Args
	 * @param array<class-string<Modifier<T_ARGS>>, ModifierRuntimeMeta<T_ARGS>> $modifiers
	 * @param RuleRuntimeMeta<Args> $rule
	 */
	public function __construct(
		array $callbacks,
		array $docs,
		array $modifiers,
		RuleRuntimeMeta $rule,
		DefaultValueMeta $default,
		PhpPropertyMeta $property
	)
	{
		parent::__construct($callbacks, $docs);
		$this->rule = $rule;
		$this->default = $default;
		$this->property = $property;
		$this->modifiers = $modifiers;
	}

	/**
	 * @template T of Args
	 * @param class-string<Modifier<T>> $type
	 * @return ModifierRuntimeMeta<T>|null
	 */
	public function getModifier(string $type): ?ModifierRuntimeMeta
	{
		return $this->modifiers[$type] ?? null;
	}

	/**
	 * @return array<mixed>
	 */
	public function __serialize(): array
	{
		return [
			'parent' => parent::__serialize(),
			'rule' => $this->rule,
			'default' => $this->default,
			'property' => $this->property,
			'modifiers' => $this->modifiers,
		];
	}

	/**
	 * @param array<mixed> $data
	 */
	public function __unserialize(array $data): void
	{
		parent::__unserialize($data['parent']);
		$this->rule = $data['rule'];
		$this->default = $data['default'];
		$this->property = $data['property'];
		$this->modifiers = $data['modifiers'];
	}

}
