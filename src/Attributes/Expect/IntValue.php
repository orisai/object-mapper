<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Expect;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\IntRule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class IntValue implements RuleAttribute
{

	private ?int $min;

	private ?int $max;

	private bool $unsigned;

	private bool $castNumericString;

	public function __construct(
		?int $min = null,
		?int $max = null,
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
		return IntRule::class;
	}

	/**
	 * {@inheritDoc}
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
