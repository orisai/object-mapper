<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Exception;

use Orisai\Exceptions\DomainException;
use Orisai\ObjectMapper\Formatting\ErrorFormatter;
use Orisai\ObjectMapper\Types\StructureType;
use function sprintf;

final class InvalidData extends DomainException implements WithTypeAndValue
{

	/** @var mixed */
	private $invalidValue;

	private StructureType $invalidType;

	/**
	 * @param mixed $invalidValue
	 * @return static
	 */
	public static function create(StructureType $invalidType, $invalidValue): self
	{
		$self = new self();
		$self->invalidValue = $invalidValue;
		$self->invalidType = $invalidType;
		$self->withMessage(
			sprintf(
				'Get validation errors from `%s` with an `%s`',
				self::class,
				ErrorFormatter::class,
			),
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
