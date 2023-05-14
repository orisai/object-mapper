<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;

/**
 * @template-extends MultiValueRuleDefinition<ArrayOfRule>
 *
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ArrayOf extends MultiValueRuleDefinition
{

	private ?RuleCompileMeta $key;

	/**
	 * @param RuleDefinition<Rule<Args>>      $item
	 * @param RuleDefinition<Rule<Args>>|null $key
	 */
	public function __construct(
		RuleDefinition $item,
		?RuleDefinition $key = null,
		?int $minItems = null,
		?int $maxItems = null,
		bool $mergeDefaults = false
	)
	{
		parent::__construct($item, $minItems, $maxItems, $mergeDefaults);
		$this->key = $key === null ? null : new RuleCompileMeta($key->getType(), $key->getArgs());
	}

	public function getType(): string
	{
		return ArrayOfRule::class;
	}

	public function getArgs(): array
	{
		$args = parent::getArgs();
		$args['key'] = $this->key;

		return $args;
	}

}
