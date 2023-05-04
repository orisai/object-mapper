<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Docs;

use Orisai\ObjectMapper\Meta\MetaDefinition;

interface DocDefinition extends MetaDefinition
{

	/**
	 * @return class-string<Doc>
	 */
	public function getType(): string;

}
