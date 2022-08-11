<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;

/**
 * @internal
 */
final class CompoundArgs implements Args
{

	/** @var array<RuleRuntimeMeta<Args>> */
	public array $rules;

	/**
	 * @param array<RuleRuntimeMeta<Args>> $rules
	 */
	public function __construct(array $rules)
	{
		$this->rules = $rules;
	}

}
