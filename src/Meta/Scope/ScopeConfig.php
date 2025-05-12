<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Scope;

//TODO - non-repeatable hint

/**
 * @readonly
 */
final class ScopeConfig
{

	public bool $required;

	public RepeatableBehavior $repeatableBehavior;

	public HierarchyPosition $hierarchyPosition;

	/** @var list<Target> */
	public array $targets;

	/**
	 * @param non-empty-list<Target> $targets
	 */
	public function __construct(
		bool $required,
		RepeatableBehavior $repeatableBehavior,
		HierarchyPosition $hierarchyPosition,
		array $targets
	)
	{
		$this->required = $required;
		$this->repeatableBehavior = $repeatableBehavior;
		$this->hierarchyPosition = $hierarchyPosition;
		$this->targets = $targets;
	}

}
