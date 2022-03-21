<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Exceptions;

use Orisai\Exceptions\DomainException;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\Type;

final class ValueDoesNotMatch extends DomainException implements WithTypeAndValue
{

	/** @var mixed */
	private $invalidValue;

	private Type $invalidType;

	/**
	 * @param mixed $invalidValue
	 */
	public static function create(Type $invalidType, $invalidValue): self
	{
		$self = new self();
		$self->invalidValue = $invalidValue;
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

	/**
	 * @return mixed
	 */
	public function getInvalidValue()
	{
		return $this->invalidValue;
	}

}
