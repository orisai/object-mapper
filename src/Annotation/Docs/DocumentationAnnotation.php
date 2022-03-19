<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Docs;

use Orisai\ObjectMapper\Annotation\BaseAnnotation;
use Orisai\ObjectMapper\Docs\Doc;

/**
 * Base interface for documentation annotations
 */
interface DocumentationAnnotation extends BaseAnnotation
{

	/**
	 * @return class-string<Doc>
	 */
	public function getType(): string;

}
