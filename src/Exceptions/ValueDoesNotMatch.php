<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Exceptions;

use Orisai\Exceptions\DomainException;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\Type;
use Orisai\ObjectMapper\Types\Value;

final class ValueDoesNotMatch extends DomainException implements WithTypeAndValue
{

	private Type $type;

	private Value $value;

	private function __construct(Type $type, Value $value)
	{
		parent::__construct();
		$this->type = $type;
		$this->value = $value;
	}

	public static function create(Type $type, Value $value): self
	{
		return new self($type, $value);
	}

	public static function createFromString(string $message, Value $value): self
	{
		return new self(new MessageType($message), $value);
	}

	public function getType(): Type
	{
		return $this->type;
	}

	public function getValue(): Value
	{
		return $this->value;
	}

}
