<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Orisai\ObjectMapper\Types\MappedObjectType;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;

final class MappedObjectTypeTest extends TestCase
{

	public function testClass(): void
	{
		$type = new MappedObjectType(DefaultsVO::class);

		self::assertSame(DefaultsVO::class, $type->getClass());
	}

}
