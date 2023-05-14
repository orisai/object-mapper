<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Attribute;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @template-implements RuleDefinition<DateTimeRule>
 *
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class DateTimeValue implements RuleDefinition
{

	/** @var class-string<DateTimeInterface> */
	private string $type;

	private string $format;

	/**
	 * @param class-string<DateTimeInterface> $type
	 */
	public function __construct(string $type = DateTimeImmutable::class, string $format = DateTimeRule::FormatIsoCompat)
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
