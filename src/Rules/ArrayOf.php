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
final class ArrayOf extends MultiValueDefinition
{

	private ?RuleDefinition $key;

	public function __construct(
		RuleDefinition $item,
		?RuleDefinition $key = null,
		?int $minItems = null,
		?int $maxItems = null,
		bool $mergeDefaults = false
	)
	{
		parent::__construct($item, $minItems, $maxItems, $mergeDefaults);
		$this->key = $key;
	}

	public function getHandler(): string
	{
		return ArrayOfRule::class;
	}

	public function getArgs(): array
	{
		$args = parent::getArgs();
		$args[ArrayOfRule::KeyRule] = $this->key;

		return $args;
	}

}
