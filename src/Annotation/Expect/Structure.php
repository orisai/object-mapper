<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Rules\Rule;
use Orisai\ObjectMapper\Rules\StructureRule;
use Orisai\ObjectMapper\ValueObject;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class Structure implements RuleAnnotation
{

	/** @var class-string<ValueObject> */
	private string $type;

	/**
	 * @param class-string<ValueObject> $type
	 */
	public function __construct(string $type)
	{
		$this->type = $type;
	}

	/**
	 * @return class-string<Rule>
	 */
	public function getType(): string
	{
		return StructureRule::class;
	}

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return [
			'type' => $this->type,
		];
	}

}
