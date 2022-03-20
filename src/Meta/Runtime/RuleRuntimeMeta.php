<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Rules\Rule;

final class RuleRuntimeMeta
{

	/** @var class-string<Rule<Args>> */
	private string $type;

	private Args $args;

	/**
	 * @param class-string<Rule<Args>> $type
	 */
	public function __construct(string $type, Args $args)
	{
		$this->type = $type;
		$this->args = $args;
	}

	/**
	 * @return class-string<Rule<Args>>
	 */
	public function getType(): string
	{
		return $this->type;
	}

	public function getArgs(): Args
	{
		return $this->args;
	}

}
