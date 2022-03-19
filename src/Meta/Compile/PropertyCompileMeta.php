<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use Orisai\ObjectMapper\Meta\RuleMeta;

final class PropertyCompileMeta extends SharedNodeCompileMeta
{

	private RuleMeta $rule;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(array $callbacks, array $docs, array $modifiers, RuleMeta $rule)
	{
		parent::__construct($callbacks, $docs, $modifiers);
		$this->rule = $rule;
	}

	public function getRule(): RuleMeta
	{
		return $this->rule;
	}

}
