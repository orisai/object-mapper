<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

use Orisai\ObjectMapper\Types\Type;

interface TypePrinter extends StructurePrinter
{

	/**
	 * @return mixed
	 */
	public function formatType(Type $type);

}
