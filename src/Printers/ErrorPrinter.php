<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

use Orisai\ObjectMapper\Exceptions\InvalidData;

interface ErrorPrinter
{

	/**
	 * @param array<string> $pathNodes
	 * @return mixed
	 */
	public function formatError(InvalidData $exception, array $pathNodes = []);

}
