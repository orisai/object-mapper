<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

use Orisai\ObjectMapper\Types\StructureType;

interface StructurePrinter
{

	/**
	 * @return mixed
	 */
	public function printType(StructureType $type);

}
