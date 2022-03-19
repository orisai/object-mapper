<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Meta\DefaultValueMeta;
use Orisai\ObjectMapper\Meta\RuleMeta;

final class PropertyRuntimeMeta extends SharedNodeRuntimeMeta
{

	private DefaultValueMeta $default;

	private RuleMeta $rule;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(
		array $callbacks,
		array $docs,
		array $modifiers,
		RuleMeta $rule,
		DefaultValueMeta $default
	)
	{
		parent::__construct($callbacks, $docs, $modifiers);
		$this->rule = $rule;
		$this->default = $default;
	}

	public function getDefault(): DefaultValueMeta
	{
		return $this->default;
	}

	public function getRule(): RuleMeta
	{
		return $this->rule;
	}

}
