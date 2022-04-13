<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

use Orisai\ObjectMapper\Types\MappedObjectType;

interface MappedObjectPrinter
{

	/**
	 * @return mixed
	 */
	public function printType(MappedObjectType $type);

}
