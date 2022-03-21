<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Exceptions;

use Orisai\Exceptions\Check\CheckedException;
use Orisai\ObjectMapper\Types\Type;

interface WithTypeAndValue extends CheckedException
{

	public function getInvalidType(): Type;

	/**
	 * @return mixed
	 */
	public function getInvalidValue();

}
