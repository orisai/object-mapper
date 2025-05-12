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
final class ArrayShape extends RuleDefinition
{

	/** @var array<int|string, RuleDefinition> */
	private array $fields;

	/**
	 * @param array<int|string, RuleDefinition> $fields
	 */
	public function __construct(array $fields)
	{
		$this->fields = $fields;
	}

	public function getHandler(): string
	{
		return ArrayShapeRule::class;
	}

	public function getArgs(): array
	{
		return [
			ArrayShapeRule::Fields => $this->fields,
		];
	}

}
