<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @template-implements RuleDefinition<IntRule>
 *
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class IntValue implements RuleDefinition
{

	private ?int $min;

	private ?int $max;

	private bool $unsigned;

	private bool $castNumericString;

	public function __construct(
		?int $min = null,
		?int $max = null,
		bool $unsigned = false,
		bool $castNumericString = false
	)
	{
		$this->min = $min;
		$this->max = $max;
		$this->unsigned = $unsigned;
		$this->castNumericString = $castNumericString;
	}

	public function getType(): string
	{
		return IntRule::class;
	}

	public function getArgs(): array
	{
		return [
			'min' => $this->min,
			'max' => $this->max,
			'unsigned' => $this->unsigned,
			'castNumericString' => $this->castNumericString,
		];
	}

}
