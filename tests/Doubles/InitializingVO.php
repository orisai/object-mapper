<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use DateTimeImmutable;
use Orisai\ObjectMapper\Attributes\Expect\DateTime;
use Orisai\ObjectMapper\Attributes\Expect\InstanceValue;
use Orisai\ObjectMapper\Attributes\Expect\Structure;
use Orisai\ObjectMapper\MappedObject;
use stdClass;

final class InitializingVO extends MappedObject
{

	/** @DateTime() */
	public DateTimeImmutable $datetime;

	/** @InstanceValue(type=stdClass::class) */
	public stdClass $instance;

	/** @Structure(DefaultsVO::class) */
	public DefaultsVO $structure;

}
