<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Expect;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\StringRule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class StringValue implements RuleAttribute
{

	private ?string $pattern;

	private ?int $minLength;

	private ?int $maxLength;

	private bool $notEmpty;

	public function __construct(
		?string $pattern = null,
		?int $minLength = null,
		?int $maxLength = null,
		bool $notEmpty = false
	)
	{
		$this->pattern = $pattern;
		$this->minLength = $minLength;
		$this->maxLength = $maxLength;
		$this->notEmpty = $notEmpty;
	}

	public function getType(): string
	{
		return StringRule::class;
	}

	public function getArgs(): array
	{
		return [
			'pattern' => $this->pattern,
			'minLength' => $this->minLength,
			'maxLength' => $this->maxLength,
			'notEmpty' => $this->notEmpty,
		];
	}

}
