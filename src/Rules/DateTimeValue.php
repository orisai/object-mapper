<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Attribute;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class DateTimeValue implements RuleDefinition
{

	/** @var class-string<DateTimeInterface> */
	private string $class;

	private string $format;

	/**
	 * @param class-string<DateTimeInterface> $class
	 */
	public function __construct(
		string $class = DateTimeImmutable::class,
		string $format = DateTimeRule::FormatIsoCompat
	)
	{
		$this->class = $class;
		$this->format = $format;
	}

	public function getType(): string
	{
		return DateTimeRule::class;
	}

	public function getArgs(): array
	{
		return [
			'class' => $this->class,
			'format' => $this->format,
		];
	}

}
