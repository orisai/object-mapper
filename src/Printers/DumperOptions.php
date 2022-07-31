<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Printers;

final class DumperOptions
{

	public bool $includeApostrophe = true;

	/** @var int<1, max> */
	public int $maxDepth = 50;

	/** @var int<1, max> */
	public int $wrapLength = 120;

	/** @var non-empty-string */
	public string $indentChar = "\t";

}
