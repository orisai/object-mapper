<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Exceptions;

use Orisai\Exceptions\DomainException;
use Orisai\ObjectMapper\Printers\ErrorPrinter;
use Orisai\ObjectMapper\Types\StructureType;
use Orisai\ObjectMapper\Types\Value;

final class InvalidData extends DomainException implements WithTypeAndValue
{

	private StructureType $type;

	private Value $value;

	public static function create(StructureType $type, Value $value): self
	{
		$self = new self();
		$self->value = $value;
		$self->type = $type;

		$selfClass = self::class;
		$printerClass = ErrorPrinter::class;
		$self->withMessage(
			"Get validation errors from `$selfClass` with an `$printerClass`",
		);

		return $self;
	}

	public function getType(): StructureType
	{
		return $this->type;
	}

	public function getValue(): Value
	{
		return $this->value;
	}

}
