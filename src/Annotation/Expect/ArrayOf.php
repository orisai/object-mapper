<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Annotation\AnnotationMetaExtractor;
use Orisai\ObjectMapper\Rules\ArrayOfRule;
use Orisai\ObjectMapper\Rules\Rule;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class ArrayOf extends MultiValueRuleAnnotation
{

	/** @var array<mixed>|null */
	private ?array $keyRule;

	public function __construct(
		RuleAnnotation $itemRule,
		?RuleAnnotation $keyRule = null,
		?int $minItems = null,
		?int $maxItems = null,
		bool $mergeDefaults = false
	)
	{
		parent::__construct($itemRule, $minItems, $maxItems, $mergeDefaults);
		$this->keyRule = $keyRule === null ? null : AnnotationMetaExtractor::extract($keyRule);
	}

	/**
	 * @return class-string<Rule>
	 */
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
		$args['keyRule'] = $this->keyRule;

		return $args;
	}

}
