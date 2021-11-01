<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Expect;

use Orisai\ObjectMapper\Annotation\BaseAnnotation;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\Meta\MetaSource;
use Orisai\ObjectMapper\Rules\Rule;

/**
 * Base interface for rule annotations
 */
interface RuleAnnotation extends BaseAnnotation
{

	public const ANNOTATION_TYPE = MetaSource::TYPE_RULE;

	/**
	 * @return class-string<Rule<Args>>
	 */
	public function getType(): string;

}
