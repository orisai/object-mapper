<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Context;

use Orisai\ObjectMapper\Context\ArgsFieldContext;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;
use Orisai\ObjectMapper\Tester\ObjectMapperTester;
use PHPUnit\Framework\TestCase;

final class ArgsFieldContextTest extends TestCase
{

	public function test(): void
	{
		$deps = (new ObjectMapperTester())->buildDependencies();
		$default = DefaultValueMeta::fromNothing();

		$context = new ArgsFieldContext($deps->metaLoader, $deps->metaResolver, $default);

		self::assertSame($deps->metaLoader, $context->getMetaLoader());
		self::assertSame($deps->metaResolver, $context->getMetaResolver());
		self::assertFalse($context->hasDefaultValue());
	}

	public function testHasDefault(): void
	{
		$deps = (new ObjectMapperTester())->buildDependencies();
		$default = DefaultValueMeta::fromValue('foo');

		$context = new ArgsFieldContext($deps->metaLoader, $deps->metaResolver, $default);

		self::assertSame($deps->metaLoader, $context->getMetaLoader());
		self::assertSame($deps->metaResolver, $context->getMetaResolver());
		self::assertTrue($context->hasDefaultValue());
		self::assertSame('foo', $context->getDefaultValue());
	}

}
