<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Callback;

use Orisai\ObjectMapper\Annotation\BaseAnnotation;
use Orisai\ObjectMapper\Callbacks\Callback;
use Orisai\ObjectMapper\Meta\MetaSource;

/**
 * Base interface for callable annotations
 */
interface CallableAnnotation extends BaseAnnotation
{

	public const ANNOTATION_TYPE = MetaSource::TYPE_CALLBACKS;

	/**
	 * @phpstan-return class-string<Callback>
	 */
	public function getType(): string;

}
