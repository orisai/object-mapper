<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ArrayShape implements RuleDefinition
{

	/** @var array<int|string, RuleCompileMeta> */
	private array $fields;

	/**
	 * @param array<int|string, RuleDefinition> $fields
	 */
	public function __construct(array $fields)
	{
		$this->fields = $this->definitionsToRules($fields);
	}

	public function getType(): string
	{
		return ArrayShapeRule::class;
	}

	public function getArgs(): array
	{
		return [
			'fields' => $this->fields,
		];
	}

	/**
	 * @param array<int|string, RuleDefinition> $definitions
	 * @return array<int|string, RuleCompileMeta>
	 */
	private function definitionsToRules(array $definitions): array
	{
		$rules = [];
		foreach ($definitions as $key => $definition) {
			$rules[$key] = new RuleCompileMeta($definition->getType(), $definition->getArgs());
		}

		return $rules;
	}

}
