<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

final class PropertyCompileMeta extends SharedNodeCompileMeta
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
