<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use ReflectionClass;
use ReflectionProperty;

final class FieldCompileMeta extends NodeCompileMeta
{

	private RuleCompileMeta $rule;

	private ReflectionProperty $property;

	public function __construct(
		array $callbacks,
		array $docs,
		array $modifiers,
		RuleCompileMeta $rule,
		ReflectionProperty $property
	)
	{
		parent::__construct($callbacks, $docs, $modifiers);
		$this->rule = $rule;
		$this->property = $property;
	}

	public function getRule(): RuleCompileMeta
	{
		return $this->rule;
	}

	public function getClass(): ReflectionClass
	{
		return $this->property->getDeclaringClass();
	}

	public function getProperty(): ReflectionProperty
	{
		return $this->property;
	}

	public function hasEqualMeta(self $meta): bool
	{
		if ($this->rule != $meta->getRule()) {
			return false;
		}

		if ($this->getCallbacks() != $meta->getCallbacks()) {
			return false;
		}

		if ($this->getDocs() != $meta->getDocs()) {
			return false;
		}

		// phpcs:disable SlevomatCodingStandard.ControlStructures.UselessIfConditionWithReturn
		if ($this->getModifiers() != $meta->getModifiers()) {
			return false;
		}

		return true;
	}

}
