<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @template-implements RuleDefinition<ArrayEnumRule>
 *
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ArrayEnumValue implements RuleDefinition
{

	/** @var array<mixed> */
	private array $cases;

	private bool $useKeys;

	/**
	 * @param array<mixed> $cases
	 */
	public function __construct(array $cases, bool $useKeys = false)
	{
		$this->cases = $cases;
		$this->useKeys = $useKeys;
	}

	public function getType(): string
	{
		return ArrayEnumRule::class;
	}

	public function getArgs(): array
	{
		return [
			'cases' => $this->cases,
			'useKeys' => $this->useKeys,
		];
	}

}
