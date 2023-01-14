<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\DefaultValueMeta;
use ReflectionProperty;

final class FieldRuntimeMeta extends NodeRuntimeMeta
{

	private DefaultValueMeta $default;

	/** @var RuleRuntimeMeta<Args> */
	private RuleRuntimeMeta $rule;

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

	public function getDefault(): DefaultValueMeta
	{
		return $this->default;
	}

	/**
	 * @return RuleRuntimeMeta<Args>
	 */
	public function getRule(): RuleRuntimeMeta
	{
		return $this->rule;
	}

	public function getProperty(): ReflectionProperty
	{
		return $this->property;
	}

}
