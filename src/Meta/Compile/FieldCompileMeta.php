<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use Orisai\ReflectionMeta\Structure\ClassStructure;
use Orisai\ReflectionMeta\Structure\PropertyStructure;

final class FieldCompileMeta extends NodeCompileMeta
{

	private RuleCompileMeta $rule;

	private ClassStructure $class;

	private PropertyStructure $property;

	public function __construct(
		array $callbacks,
		array $docs,
		array $modifiers,
		RuleCompileMeta $rule,
		PropertyStructure $property
	)
	{
		parent::__construct($callbacks, $docs, $modifiers);
		$this->rule = $rule;
		$this->class = new ClassStructure(
			$property->getContextReflector()->getDeclaringClass(),
			$property->getSource()->getClass(),
		);
		$this->property = $property;
	}

	public function getRule(): RuleCompileMeta
	{
		return $this->rule;
	}

	public function getClass(): ClassStructure
	{
		return $this->class;
	}

	public function getProperty(): PropertyStructure
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
