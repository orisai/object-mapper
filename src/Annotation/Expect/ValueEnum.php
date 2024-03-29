<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Rules\ValueEnumRule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class ValueEnum implements RuleAnnotation
{

	/** @var array<mixed> */
	private array $values;

	private bool $useKeys;

	/**
	 * @param array<mixed> $values
	 */
	public function __construct(array $values, bool $useKeys = false)
	{
		$this->values = $values;
		$this->useKeys = $useKeys;
	}

	/**
	 * @return class-string<Rule>
	 */
	public function getType(): string
	{
		return ValueEnumRule::class;
	}

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return [
			'values' => $this->values,
			'useKeys' => $this->useKeys,
		];
	}

}
