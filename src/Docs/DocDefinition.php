<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Orisai\ObjectMapper\Meta\MetaDefinition;

abstract class DocDefinition implements MetaDefinition
{

	/**
	 * @return class-string<Doc>
	 */
	abstract public function getHandler(): string;

}
