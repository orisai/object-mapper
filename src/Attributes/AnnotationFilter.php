<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes;

use Nette\Utils\Strings;

final class AnnotationFilter
{

	/**
	 * Remove useless whitespace and * from start of every line
	 */
	public static function filterMultilineDocblock(string $docblock): string
	{
		return Strings::replace($docblock, '#\s*\*\/$|^\s*\*\s{0,1}|^\/\*{1,2}#m', '');
	}

}
