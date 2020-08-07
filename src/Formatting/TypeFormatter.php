<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Formatting;

use Orisai\ObjectMapper\Types\Type;

interface TypeFormatter extends StructureFormatter
{

	/**
	 * @return mixed
	 */
	public function formatType(Type $type);

}
