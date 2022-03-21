<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Expect;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\FloatRule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class FloatValue implements RuleAttribute
{

	private ?float $min;

	private ?float $max;

	private bool $unsigned;

	private bool $castNumericString;

	public function __construct(
		?float $min = null,
		?float $max = null,
		bool $unsigned = true,
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
		return FloatRule::class;
	}

	/**
	 * @return array<mixed>
	 */
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
