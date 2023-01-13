<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\DefaultValueMeta;
use ReflectionClass;

final class PropertyRuntimeMeta extends NodeRuntimeMeta
{

	private DefaultValueMeta $default;

	/** @var RuleRuntimeMeta<Args> */
	private RuleRuntimeMeta $rule;

	/** @var ReflectionClass<MappedObject> */
	private ReflectionClass $declaringClass;

	/**
	 * @param RuleRuntimeMeta<Args>         $rule
	 * @param ReflectionClass<MappedObject> $declaringClass
	 */
	public function __construct(
		array $callbacks,
		array $docs,
		array $modifiers,
		RuleRuntimeMeta $rule,
		DefaultValueMeta $default,
		ReflectionClass $declaringClass
	)
	{
		parent::__construct($callbacks, $docs, $modifiers);
		$this->rule = $rule;
		$this->default = $default;
		$this->declaringClass = $declaringClass;
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

	/**
	 * @return ReflectionClass<MappedObject>
	 */
	public function getDeclaringClass(): ReflectionClass
	{
		return $this->declaringClass;
	}

}
