<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Exception;

use Orisai\Exceptions\Check\CheckedException;
use Orisai\ObjectMapper\Types\Type;
use Orisai\ObjectMapper\Types\Value;

interface WithTypeAndValue extends CheckedException
{

	public function getType(): Type;

	public function getValue(): Value;

	public function dropValue(): void;

}
