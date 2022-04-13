<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

use Orisai\ObjectMapper\Types\Type;

interface TypePrinter extends MappedObjectPrinter
{

	/**
	 * @return mixed
	 */
	public function printType(Type $type);

}
