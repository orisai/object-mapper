<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Orisai\ObjectMapper\Meta\BaseAttribute;

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
