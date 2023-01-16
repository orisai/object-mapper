<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Rules\Rule;

final class RuleCompileMeta
{

	/** @var class-string<Rule<Args>> */
	private string $type;

	/** @var array<mixed> */
	private array $args;

	/**
	 * @param class-string<Rule<Args>> $type
	 * @param array<mixed>             $args
	 */
	public function __construct(string $type, array $args = [])
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

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return $this->args;
	}

}
