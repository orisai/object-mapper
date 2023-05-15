<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Rules\Rule;

/**
 * @template-covariant T of Args
 */
final class RuleRuntimeMeta
{

	/** @var class-string<Rule<T>> */
	private string $type;

	/** @var T */
	private Args $args;

	/**
	 * @param class-string<Rule<T>> $type
	 * @param T $args
	 */
	public function __construct(string $type, Args $args)
	{
		$this->type = $type;
		$this->args = $args;
	}

	/**
	 * @return class-string<Rule<T>>
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return T
	 */
	public function getArgs(): Args
	{
		return $this->args;
	}

}
