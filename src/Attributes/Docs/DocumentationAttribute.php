<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes\Docs;

use Orisai\ObjectMapper\Attributes\BaseAttribute;
use Orisai\ObjectMapper\Docs\Doc;

/**
 * Base interface for documentation annotations
 */
interface DocumentationAttribute extends BaseAttribute
{

	/**
	 * @return class-string<Doc>
	 */
	public function getType(): string;

}
