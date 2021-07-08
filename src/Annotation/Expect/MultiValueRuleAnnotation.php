<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Orisai\ObjectMapper\Annotation\AnnotationMetaExtractor;

abstract class MultiValueRuleAnnotation implements RuleAnnotation
{

	/** @var array<mixed> */
	private array $itemRule;

	private ?int $minItems;

	private ?int $maxItems;

	private bool $mergeDefaults;

	public function __construct(
		RuleAnnotation $itemRule,
		?int $minItems = null,
		?int $maxItems = null,
		bool $mergeDefaults = false
	)
	{
		$this->itemRule = AnnotationMetaExtractor::extract($itemRule);
		$this->minItems = $minItems;
		$this->maxItems = $maxItems;
		$this->mergeDefaults = $mergeDefaults;
	}

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array
	{
		return [
			'itemRule' => $this->itemRule,
			'minItems' => $this->minItems,
			'maxItems' => $this->maxItems,
			'mergeDefaults' => $this->mergeDefaults,
		];
	}

}
