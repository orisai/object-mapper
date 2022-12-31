<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use DateTimeImmutable;
use Orisai\ObjectMapper\Attributes\Expect\DateTimeValue;
use Orisai\ObjectMapper\Attributes\Expect\InstanceOfValue;
use Orisai\ObjectMapper\Attributes\Expect\MappedObjectValue;
use Orisai\ObjectMapper\MappedObject;
use stdClass;

final class InitializingVO implements MappedObject
{

	/** @DateTimeValue() */
	public DateTimeImmutable $datetime;

	/** @InstanceOfValue(type=stdClass::class) */
	public stdClass $instance;

	/** @MappedObjectValue(DefaultsVO::class) */
	public DefaultsVO $structure;

}
