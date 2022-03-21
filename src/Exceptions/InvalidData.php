<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Exceptions;

use Orisai\Exceptions\DomainException;
use Orisai\ObjectMapper\Printers\ErrorPrinter;
use Orisai\ObjectMapper\Types\StructureType;

final class InvalidData extends DomainException implements WithTypeAndValue
{

	/** @var mixed */
	private $invalidValue;

	private StructureType $invalidType;

	/**
	 * @param mixed $invalidValue
	 */
	public static function create(StructureType $invalidType, $invalidValue): self
	{
		$self = new self();
		$self->invalidValue = $invalidValue;
		$self->invalidType = $invalidType;

		$selfClass = self::class;
		$printerClass = ErrorPrinter::class;
		$self->withMessage(
			"Get validation errors from `$selfClass` with an `$printerClass`",
		);

		return $self;
	}

	public function getInvalidType(): StructureType
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
