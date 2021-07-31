<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Exception;

use Orisai\ObjectMapper\Types\Type;

interface WithTypeAndValue
{

	function getInvalidType(): Type;

	/**
	 * @return mixed
	 */
	function getInvalidValue();

}
