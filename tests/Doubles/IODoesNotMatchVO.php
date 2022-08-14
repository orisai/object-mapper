<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use DateTimeImmutable;
use Orisai\ObjectMapper\Attributes\Expect\BoolValue;
use Orisai\ObjectMapper\Attributes\Expect\DateTimeValue;
use Orisai\ObjectMapper\MappedObject;

final class IODoesNotMatchVO extends MappedObject
{

	/** @BoolValue(castBoolLike=true) */
	public bool $bool;

	/** @DateTimeValue() */
	public DateTimeImmutable $dateTime;

}
