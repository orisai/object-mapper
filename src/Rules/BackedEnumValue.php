<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Attribute;
use BackedEnum;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class BackedEnumValue extends RuleDefinition
{

	/** @var class-string<BackedEnum> */
	private string $class;

	private bool $allowUnknown;

	/**
	 * @param class-string<BackedEnum> $class
	 */
	public function __construct(string $class, bool $allowUnknown = false)
	{
		$this->class = $class;
		$this->allowUnknown = $allowUnknown;
	}

	public function getHandler(): string
	{
		return BackedEnumRule::class;
	}

	public function getArgs(): array
	{
		return [
			BackedEnumRule::ClassName => $this->class,
			BackedEnumRule::AllowUnknown => $this->allowUnknown,
		];
	}

}
