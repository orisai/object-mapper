<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Orisai\ObjectMapper\Types\SimpleValueType;
use PHPUnit\Framework\TestCase;

final class SimpleValueTypeTest extends TestCase
{

	public function testType(): void
	{
		$type = new SimpleValueType('string');

		self::assertSame('string', $type->getName());
	}

}
