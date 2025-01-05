<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Context;

use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\Tester\ObjectMapperTester;
use PHPUnit\Framework\TestCase;

final class ArgsContextTest extends TestCase
{

	public function test(): void
	{
		$deps = (new ObjectMapperTester())->buildDependencies();

		$context = new ArgsContext($deps->metaLoader, $deps->metaResolver);

		self::assertSame($deps->metaLoader, $context->getMetaLoader());
		self::assertSame($deps->metaResolver, $context->getMetaResolver());
	}

}
