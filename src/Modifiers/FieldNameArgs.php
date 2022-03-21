<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Modifiers;

use Orisai\ObjectMapper\Args\Args;

final class FieldNameArgs implements Args
{

	/** @var int|string */
	public $name;

	/**
	 * @param int|string $name
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

}
