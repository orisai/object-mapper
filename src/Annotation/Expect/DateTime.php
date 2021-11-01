<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\DateTimeRule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class DateTime implements RuleAnnotation
{

	/** @var class-string<DateTimeInterface> */
	private string $type;

	private ?string $format;

	/**
	 * @param class-string<DateTimeInterface> $type
	 */
	public function __construct(string $type = DateTimeImmutable::class, ?string $format = null)
	{
		$this->type = $type;
		$this->format = $format;
	}

	public function getType(): string
	{
		return DateTimeRule::class;
	}

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return [
			'type' => $this->type,
			'format' => $this->format,
		];
	}

}
