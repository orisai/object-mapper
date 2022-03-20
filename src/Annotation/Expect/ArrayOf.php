<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Rules\ArrayOfRule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class ArrayOf extends MultiValueRuleAnnotation
{

	private ?RuleCompileMeta $key;

	public function __construct(
		RuleAnnotation $item,
		?RuleAnnotation $key = null,
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

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		$args = parent::getArgs();
		$args['key'] = $this->key;

		return $args;
	}

}
