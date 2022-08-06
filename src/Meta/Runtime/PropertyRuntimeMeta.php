<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\DefaultValueMeta;

final class PropertyRuntimeMeta extends NodeRuntimeMeta
{

	private DefaultValueMeta $default;

	/** @var RuleRuntimeMeta<Args> */
	private RuleRuntimeMeta $rule;

	/**
	 * @param RuleRuntimeMeta<Args> $rule
	 */
	public function __construct(
		array $callbacks,
		array $docs,
		array $modifiers,
		RuleRuntimeMeta $rule,
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

	/**
	 * @return RuleRuntimeMeta<Args>
	 */
	public function getRule(): RuleRuntimeMeta
	{
		return $this->rule;
	}

}
