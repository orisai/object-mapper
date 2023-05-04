<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use DateTimeImmutable;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\DateTimeValue;
use Orisai\ObjectMapper\Rules\InstanceOfValue;
use Orisai\ObjectMapper\Rules\MappedObjectValue;
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
