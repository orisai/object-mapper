<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Exception;

use Orisai\Exceptions\DomainException;
use Orisai\ObjectMapper\Printers\ErrorPrinter;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Types\MappedObjectType;

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

	public function dropValue(): void
	{
		$this->value = Value::none();
	}

}
