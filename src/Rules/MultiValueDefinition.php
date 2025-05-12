<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

abstract class MultiValueDefinition extends RuleDefinition
{

	private RuleDefinition $item;

	private ?int $minItems;

	private ?int $maxItems;

	private bool $mergeDefaults;

	public function __construct(
		RuleDefinition $item,
		?int $minItems = null,
		?int $maxItems = null,
		bool $mergeDefaults = false
	)
	{
		$this->item = $item;
		$this->minItems = $minItems;
		$this->maxItems = $maxItems;
		$this->mergeDefaults = $mergeDefaults;
	}

	public function getArgs(): array
	{
		return [
			MultiValueRule::ItemRule => $this->item,
			MultiValueRule::MinItems => $this->minItems,
			MultiValueRule::MaxItems => $this->maxItems,
			MultiValueRule::MergeDefaults => $this->mergeDefaults,
		];
	}

}
