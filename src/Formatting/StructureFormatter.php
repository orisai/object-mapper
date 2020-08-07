<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Formatting;

use Orisai\ObjectMapper\Types\StructureType;

interface StructureFormatter
{

	/**
	 * @return mixed
	 */
	public function formatType(StructureType $type);

}
