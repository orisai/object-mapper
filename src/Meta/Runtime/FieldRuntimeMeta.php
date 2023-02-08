<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\DefaultValueMeta;
use ReflectionProperty;

final class FieldRuntimeMeta extends NodeRuntimeMeta
{

	/** @var RuleRuntimeMeta<Args> */
	private RuleRuntimeMeta $rule;

	private DefaultValueMeta $default;

	private ReflectionProperty $property;

	/**
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
		parent::__construct($callbacks, $docs, $modifiers);
		$this->rule = $rule;
		$this->default = $default;
		$this->property = $property;
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
	}

}
