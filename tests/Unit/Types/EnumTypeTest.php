<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Orisai\ObjectMapper\Types\EnumType;
use PHPUnit\Framework\TestCase;

final class EnumTypeTest extends TestCase
{

	public function testType(): void
	{
		$cases = ['lorem', 'ipsum', 123];

		$type = new EnumType($cases);
		self::assertSame($cases, $type->getCases());
	}

}
