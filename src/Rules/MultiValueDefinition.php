<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;

/**
 * @template-covariant T of Rule
 * @implements RuleDefinition<T>
 */
abstract class MultiValueDefinition implements RuleDefinition
{

	private RuleCompileMeta $item;

	private ?int $minItems;

	private ?int $maxItems;

	private bool $mergeDefaults;

	/**
	 * @param RuleDefinition<Rule<Args>> $item
	 */
	public function __construct(
		RuleDefinition $item,
		?int $minItems = null,
		?int $maxItems = null,
		bool $mergeDefaults = false
	)
	{
		$this->item = new RuleCompileMeta($item->getType(), $item->getArgs());
		$this->minItems = $minItems;
		$this->maxItems = $maxItems;
		$this->mergeDefaults = $mergeDefaults;
	}

	public function getArgs(): array
	{
		return [
			'item' => $this->item,
			'minItems' => $this->minItems,
			'maxItems' => $this->maxItems,
			'mergeDefaults' => $this->mergeDefaults,
		];
	}

}
