<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Types;

use Generator;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Types\ArrayType;
use Orisai\ObjectMapper\Types\ListType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\ParametrizedType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use PHPUnit\Framework\TestCase;

final class ParametrizedTypeTest extends TestCase
{

	/**
	 * @dataProvider provideType
	 */
	public function testParameters(ParametrizedType $type): void
	{
		$type->addKeyValueParameter('a', 'b');
		$type->addKeyParameter('c');
		$type->addKeyParameter('d');

		$parameters = $type->getParameters();
		self::assertCount(3, $parameters);
		self::assertFalse($type->hasInvalidParameters());

		$p1 = $parameters['a'];
		self::assertSame('a', $p1->getKey());
		self::assertTrue($p1->hasValue());
		self::assertSame('b', $p1->getValue());
		self::assertTrue($type->hasParameter('a'));
		self::assertSame($p1, $type->getParameter('a'));

		$p2 = $parameters['c'];
		self::assertSame('c', $p2->getKey());
		self::assertFalse($p2->hasValue());
		self::assertTrue($type->hasParameter('c'));
		self::assertSame($p2, $type->getParameter('c'));

		$p3 = $parameters['d'];
		self::assertSame('d', $p3->getKey());
		self::assertFalse($p3->hasValue());
		self::assertTrue($type->hasParameter('d'));
		self::assertSame($p3, $type->getParameter('d'));

		$type->markParameterInvalid('a');
		$type->markParametersInvalid(['c', 'd']);
		self::assertTrue($type->hasInvalidParameters());

		self::assertTrue($p1->isInvalid());
		self::assertTrue($p2->isInvalid());
		self::assertTrue($p3->isInvalid());
	}

	/**
	 * @return Generator<array<ParametrizedType>>
	 */
	public function provideType(): Generator
	{
		yield [
			new SimpleValueType('name'),
		];

		yield [
			new ArrayType(null, new SimpleValueType('test')),
		];

		yield [
			new ListType(new SimpleValueType('test')),
		];
	}

	public function testParameterNeverSet(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage('Cannot get parameter `a` because it was never set');

		$type = new ArrayType(null, new MessageType('test'));
		$type->markParameterInvalid('a');
	}

}
