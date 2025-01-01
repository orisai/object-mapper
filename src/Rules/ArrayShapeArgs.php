<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;

/**
 * @internal
 */
final class ArrayShapeArgs implements Args
{

	/** @var array<int|string, RuleRuntimeMeta<Args>> */
	public array $fields;

	/**
	 * @param array<int|string, RuleRuntimeMeta<Args>> $fields
	 */
	public function __construct(array $fields)
	{
		$this->fields = $fields;
	}

}
