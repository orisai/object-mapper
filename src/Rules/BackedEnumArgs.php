<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use BackedEnum;
use Orisai\ObjectMapper\Args\Args;

final class BackedEnumArgs implements Args
{

	/** @var class-string<BackedEnum> */
	public string $class;

	public bool $allowUnknown;

	/**
	 * @param class-string<BackedEnum> $class
	 */
	public function __construct(string $class, bool $allowUnknown = false)
	{
		$this->class = $class;
		$this->allowUnknown = $allowUnknown;
	}

}
