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
final class ArrayEnumValue extends RuleDefinition
{

	/** @var array<mixed> */
	private array $cases;

	private bool $useKeys;

	private bool $allowUnknown;

	/**
	 * @param array<mixed> $cases
	 */
	public function __construct(array $cases, bool $useKeys = false, bool $allowUnknown = false)
	{
		$this->cases = $cases;
		$this->useKeys = $useKeys;
		$this->allowUnknown = $allowUnknown;
	}

	public function getHandler(): string
	{
		return ArrayEnumRule::class;
	}

	public function getArgs(): array
	{
		return [
			ArrayEnumRule::Cases => $this->cases,
			ArrayEnumRule::UseKeys => $this->useKeys,
			ArrayEnumRule::AllowUnknown => $this->allowUnknown,
		];
	}

}
