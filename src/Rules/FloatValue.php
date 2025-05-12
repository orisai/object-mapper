<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class FloatValue extends RuleDefinition
{

	private ?float $min;

	private ?float $max;

	private bool $unsigned;

	private bool $castNumericString;

	public function __construct(
		?float $min = null,
		?float $max = null,
		bool $unsigned = false,
		bool $castNumericString = false
	)
	{
		$this->min = $min;
		$this->max = $max;
		$this->unsigned = $unsigned;
		$this->castNumericString = $castNumericString;
	}

	public function getHandler(): string
	{
		return FloatRule::class;
	}

	public function getArgs(): array
	{
		return [
			FloatRule::Min => $this->min,
			FloatRule::Max => $this->max,
			FloatRule::Unsigned => $this->unsigned,
			FloatRule::CastNumericString => $this->castNumericString,
		];
	}

}
