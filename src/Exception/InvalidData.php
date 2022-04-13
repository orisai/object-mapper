<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Exception;

use Orisai\Exceptions\DomainException;
use Orisai\ObjectMapper\Printers\ErrorPrinter;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\Value;

final class InvalidData extends DomainException implements WithTypeAndValue
{

	private MappedObjectType $type;

	private Value $value;

	public static function create(MappedObjectType $type, Value $value): self
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

	public function getType(): MappedObjectType
	{
		return $this->type;
	}

	public function getValue(): Value
	{
		return $this->value;
	}

}
