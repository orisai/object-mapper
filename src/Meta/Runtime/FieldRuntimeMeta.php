<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;
use Orisai\ObjectMapper\Modifiers\Modifier;
use ReflectionProperty;

final class FieldRuntimeMeta extends NodeRuntimeMeta
{

	/** @var RuleRuntimeMeta<Args> */
	private RuleRuntimeMeta $rule;

	private DefaultValueMeta $default;

	private ReflectionProperty $property;

	/** @var array<class-string<Modifier<Args>>, ModifierRuntimeMeta<Args>> */
	private array $modifiers;

	/**
	 * @param array<class-string<Modifier<Args>>, ModifierRuntimeMeta<Args>> $modifiers
	 * @param RuleRuntimeMeta<Args> $rule
	 */
	public function __construct(
		array $callbacks,
		array $docs,
		array $modifiers,
		RuleRuntimeMeta $rule,
		DefaultValueMeta $default,
		ReflectionProperty $property
	)
	{
		parent::__construct($callbacks, $docs);
		$this->rule = $rule;
		$this->default = $default;
		$this->property = $property;
		$this->modifiers = $modifiers;
	}

	/**
	 * @return RuleRuntimeMeta<Args>
	 */
	public function getRule(): RuleRuntimeMeta
	{
		return $this->rule;
	}

	public function getDefault(): DefaultValueMeta
	{
		return $this->default;
	}

	public function getProperty(): ReflectionProperty
	{
		return $this->property;
	}

	/**
	 * @return array<class-string<Modifier<Args>>, ModifierRuntimeMeta<Args>>
	 */
	public function getModifiers(): array
	{
		return $this->modifiers;
	}

	/**
	 * @template T of Args
	 * @param class-string<Modifier<T>> $type
	 * @return ModifierRuntimeMeta<T>|null
	 */
	public function getModifier(string $type): ?ModifierRuntimeMeta
	{
		return $this->getModifiers()[$type] ?? null;
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
			'class' => $this->property->getDeclaringClass()->getName(),
			'property' => $this->property->getName(),
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
		$this->property = new ReflectionProperty($data['class'], $data['property']);
		$this->modifiers = $data['modifiers'];
	}

}
