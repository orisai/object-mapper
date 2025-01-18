<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Context;

use Orisai\ObjectMapper\Context\MappedObjectContext;
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Tester\ObjectMapperTester;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\Type;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;

final class MappedObjectContextTest extends TestCase
{

	public function test(): void
	{
		$deps = (new ObjectMapperTester())->buildDependencies();
		$options = new Options();
		$typeCreator = static fn (): Type => new MappedObjectType(DefaultsVO::class);

		$context = new MappedObjectContext(
			$deps->metaLoader,
			$deps->ruleManager,
			$deps->processor,
			$options,
			$typeCreator,
			true,
		);

		self::assertSame($deps->processor, $context->getProcessor());
		self::assertTrue($context->shouldInitializeObjects());

		self::assertNull($context->getTypeIfInitialized());

		self::assertEquals($typeCreator(), $context->getType());
		self::assertNotSame($typeCreator(), $context->getType());
		self::assertSame($context->getType(), $context->getType());

		self::assertSame($context->getType(), $context->getTypeIfInitialized());
	}

}
