<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Rules\StringRule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class StringValue implements RuleAnnotation
{

	private ?string $pattern;

	private ?string $minLength;

	private ?string $maxLength;

	private bool $notEmpty;

	public function __construct(
		?string $pattern = null,
		?string $minLength = null,
		?string $maxLength = null,
		bool $notEmpty = false
	)
	{
		$this->pattern = $pattern;
		$this->minLength = $minLength;
		$this->maxLength = $maxLength;
		$this->notEmpty = $notEmpty;
	}

	/**
	 * @return class-string<Rule>
	 */
	public function getType(): string
	{
		return StringRule::class;
	}

	/**
	 * @return array<mixed>
	 */
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
