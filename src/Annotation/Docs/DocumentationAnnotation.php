<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Docs;

use Orisai\ObjectMapper\Annotation\BaseAnnotation;
use Orisai\ObjectMapper\Docs\Doc;
use Orisai\ObjectMapper\Meta\MetaSource;

/**
 * Base interface for documentation annotations
 */
interface DocumentationAnnotation extends BaseAnnotation
{

	public const ANNOTATION_TYPE = MetaSource::TYPE_DOCS;

	/**
	 * @phpstan-return class-string<Doc>
	 */
	public function getType(): string;

}
