<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

final class FieldCompileMeta extends NodeCompileMeta
{

	private RuleCompileMeta $rule;

	public function __construct(array $callbacks, array $docs, array $modifiers, RuleCompileMeta $rule)
	{
		parent::__construct($callbacks, $docs, $modifiers);
		$this->rule = $rule;
	}

	public function getRule(): RuleCompileMeta
	{
		return $this->rule;
	}

}
