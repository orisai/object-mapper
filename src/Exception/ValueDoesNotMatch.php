<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Exception;

use Orisai\Exceptions\DomainException;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\Type;

final class ValueDoesNotMatch extends DomainException
{

	private Type $invalidType;

	public static function create(Type $invalidType): self
	{
		$self = new self();
		$self->invalidType = $invalidType;

		return $self;
	}

	public static function createFromString(string $message): self
	{
		$self = new self();
		$self->invalidType = new MessageType($message);

		return $self;
	}

	public function getInvalidType(): Type
	{
		return $this->invalidType;
	}

}
