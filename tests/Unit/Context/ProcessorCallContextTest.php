<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Context;

use Orisai\ObjectMapper\Context\ProcessorCallContext;
use Orisai\ObjectMapper\Processing\ObjectCreator;
use Orisai\ObjectMapper\Processing\ObjectHolder;
use Orisai\ObjectMapper\Tester\ObjectMapperTester;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;

final class ProcessorCallContextTest extends TestCase
{

	public function test(): void
	{
		$deps = (new ObjectMapperTester())->buildDependencies();
		$meta = $deps->metaLoader->load(DefaultsVO::class);
		$holder = new ObjectHolder(
			new ObjectCreator($deps->dependencyInjectorManager),
			DefaultsVO::class,
			$meta->getClass(),
			null,
		);

		$context = new ProcessorCallContext(DefaultsVO::class, $holder, $meta);

		self::assertSame(DefaultsVO::class, $context->getClass());
		self::assertSame($holder, $context->getObjectHolder());
		self::assertSame($meta, $context->getMeta());
	}

}
