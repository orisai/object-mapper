<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Context;

use Orisai\ObjectMapper\Meta\Context\MetaContext;
use Orisai\ObjectMapper\Tester\ObjectMapperTester;
use PHPUnit\Framework\TestCase;

final class MetaContextTest extends TestCase
{

	public function test(): void
	{
		$deps = (new ObjectMapperTester())->buildDependencies();

		$context = new MetaContext($deps->metaLoader, $deps->metaResolver);

		self::assertSame($deps->metaLoader, $context->getMetaLoader());
		self::assertSame($deps->metaResolver, $context->getMetaResolver());
	}

}
