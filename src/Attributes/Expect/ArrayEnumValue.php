<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Expect;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\ArrayEnumRule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class ArrayEnumValue implements RuleAttribute
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

	public function getType(): string
	{
		return ArrayEnumRule::class;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getArgs(): array
	{
		return [
			'values' => $this->values,
			'useKeys' => $this->useKeys,
		];
	}

}
