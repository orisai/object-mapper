<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Expect;

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
final class DateTimeValue implements RuleAttribute
{

	/** @var class-string<DateTimeInterface> */
	private string $type;

	private string $format;

	/**
	 * @param class-string<DateTimeInterface> $type
	 */
	public function __construct(string $type = DateTimeImmutable::class, string $format = DateTimeInterface::ATOM)
	{
		$this->type = $type;
		$this->format = $format;
	}

	public function getType(): string
	{
		return DateTimeRule::class;
	}

	public function getArgs(): array
	{
		return [
			'type' => $this->type,
			'format' => $this->format,
		];
	}

}
