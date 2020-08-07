<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Orisai\ObjectMapper\Types\EnumType;
use PHPUnit\Framework\TestCase;

final class EnumTypeTest extends TestCase
{

	public function testType(): void
	{
		$values = ['lorem', 'ipsum', 123];

		$type = new EnumType($values);
		self::assertSame($values, $type->getValues());
	}

}
