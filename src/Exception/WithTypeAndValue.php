<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Exception;

use Orisai\Exceptions\Check\CheckedException;
use Orisai\ObjectMapper\Types\Type;

interface WithTypeAndValue extends CheckedException
{

	function getInvalidType(): Type;

	/**
	 * @return mixed
	 */
	function getInvalidValue();

}
