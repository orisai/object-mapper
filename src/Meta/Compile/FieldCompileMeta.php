<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use Orisai\ReflectionMeta\Structure\ClassStructure;
use Orisai\ReflectionMeta\Structure\PropertyStructure;

final class FieldCompileMeta extends NodeCompileMeta
{

	/** @var list<RuleCompileMeta> */
	private array $rules;

	private ClassStructure $class;

	private PropertyStructure $property;

	/**
	 * @param list<RuleCompileMeta> $rules
	 */
	public function __construct(
		array $callbacks,
		array $docs,
		array $modifiers,
		array $rules,
		PropertyStructure $property
	)
	{
		parent::__construct($callbacks, $docs, $modifiers);
		$this->rules = $rules;
		$this->class = new ClassStructure(
			$property->getContextReflector()->getDeclaringClass(),
			$property->getSource()->getClass(),
		);
		$this->property = $property;
	}

	/**
	 * @return list<RuleCompileMeta>
	 */
	public function getRules(): array
	{
		return $this->rules;
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
		if ($this->rules != $meta->getRules()) {
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
