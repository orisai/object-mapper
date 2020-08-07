<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Formatting;

use Orisai\ObjectMapper\Exception\InvalidData;

interface ErrorFormatter
{

	/**
	 * @param array<string> $pathNodes
	 * @return mixed
	 */
	public function formatError(InvalidData $exception, array $pathNodes = []);

}
