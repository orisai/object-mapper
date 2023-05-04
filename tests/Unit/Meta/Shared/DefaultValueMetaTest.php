<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Shared;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;
use PHPUnit\Framework\TestCase;

final class DefaultValueMetaTest extends TestCase
{

	public function testNothing(): void
	{
		$meta = DefaultValueMeta::fromNothing();
		self::assertFalse($meta->hasValue());

		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			'Check if default value exists with Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta::hasValue()',
		);
		$meta->getValue();
	}

	public function testValue(): void
	{
		$value = 'value';
		$meta = DefaultValueMeta::fromValue($value);
		self::assertTrue($meta->hasValue());
		self::assertSame($value, $meta->getValue());
	}

}
