<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Processing\Context;

use Orisai\ObjectMapper\Processing\Context\ProcessorCallContext;
use Orisai\ObjectMapper\Processing\ObjectCreator;
use Orisai\ObjectMapper\Processing\ObjectHolder;
use Orisai\ObjectMapper\Tester\ObjectMapperTester;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\Type;
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
			$meta->class,
		);
		$typeCreator = static fn (): Type => new MappedObjectType(DefaultsVO::class);

		$context = new ProcessorCallContext($holder, $meta, $typeCreator);

		self::assertSame($holder, $context->getObjectHolder());
		self::assertSame($meta, $context->getMeta());

		self::assertNull($context->getTypeIfInitialized());

		self::assertEquals($typeCreator(), $context->getType());
		self::assertNotSame($typeCreator(), $context->getType());
		self::assertSame($context->getType(), $context->getType());

		self::assertSame($context->getType(), $context->getTypeIfInitialized());
	}

}
