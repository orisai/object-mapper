<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Types\TypeParameter;
use PHPUnit\Framework\TestCase;

final class TypeParameterTest extends TestCase
{

	public function testKey(): void
	{
		$parameter = TypeParameter::fromKey(123);
		self::assertSame(123, $parameter->getKey());

		$parameter2 = TypeParameter::fromKey('test');
		self::assertSame('test', $parameter2->getKey());
		self::assertFalse($parameter2->hasValue());

		self::assertFalse($parameter2->isInvalid());
		$parameter2->markInvalid();
		self::assertTrue($parameter2->isInvalid());
	}

	public function testKeyValue(): void
	{
		$parameter = TypeParameter::fromKeyAndValue(123, 'value');
		self::assertSame(123, $parameter->getKey());

		$parameter2 = TypeParameter::fromKeyAndValue('test', 'value');
		self::assertSame('test', $parameter2->getKey());
		self::assertTrue($parameter2->hasValue());
		self::assertSame('value', $parameter2->getValue());

		self::assertFalse($parameter2->isInvalid());
		$parameter2->markInvalid();
		self::assertTrue($parameter2->isInvalid());
	}

	public function testNoValueFailure(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectDeprecationMessage(
			'Cannot access value of parameter which does not have one. Check with `Orisai\ObjectMapper\Types\TypeParameter->hasValue()`.',
		);

		$parameter = TypeParameter::fromKey(123);
		$parameter->getValue();
	}

}
