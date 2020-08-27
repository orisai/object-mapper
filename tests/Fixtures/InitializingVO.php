<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use DateTimeImmutable;
use Orisai\ObjectMapper\Annotation\Expect\DateTime;
use Orisai\ObjectMapper\Annotation\Expect\InstanceValue;
use Orisai\ObjectMapper\Annotation\Expect\Structure;
use Orisai\ObjectMapper\ValueObject;
use stdClass;

final class InitializingVO extends ValueObject
{

	/** @DateTime() */
	public DateTimeImmutable $datetime;

	/** @InstanceValue(type=stdClass::class) */
	public stdClass $instance;

	/** @Structure(DefaultsVO::class) */
	public DefaultsVO $structure;

}
