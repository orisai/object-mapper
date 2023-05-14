<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Attribute;
use BackedEnum;

/**
 * @implements RuleDefinition<BackedEnumRule>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class BackedEnumValue implements RuleDefinition
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

	public function getType(): string
	{
		return BackedEnumRule::class;
	}

	public function getArgs(): array
	{
		return [
			'class' => $this->class,
			'allowUnknown' => $this->allowUnknown,
		];
	}

}
