<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use DateTimeImmutable;
use Orisai\ObjectMapper\Annotation\Expect;
use Orisai\ObjectMapper\ValueObject;
use stdClass;

final class InitializingVO extends ValueObject
{

	/** @Expect\DateTime() */
	public DateTimeImmutable $datetime;

	/** @Expect\InstanceValue(type=stdClass::class) */
	public stdClass $instance;

	/** @Expect\Structure(DefaultsVO::class) */
	public DefaultsVO $structure;

}
