<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Context;

use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Tester\ObjectMapperTester;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Type;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;

final class FieldContextTest extends TestCase
{

	public function test(): void
	{
		$deps = (new ObjectMapperTester())->buildDependencies();
		$options = new Options();
		$typeCreator = static fn (): Type => new SimpleValueType('string');
		$default = DefaultValueMeta::fromNothing();
		$fieldName = 'field';
		$property = new ReflectionProperty(DefaultsVO::class, 'string');

		$context = new FieldContext(
			$deps->metaLoader,
			$deps->ruleManager,
			$deps->processor,
			$options,
			$typeCreator,
			$default,
			true,
			$fieldName,
			$property,
		);

		self::assertSame($deps->processor, $context->getProcessor());
		self::assertTrue($context->shouldInitializeObjects());

		self::assertFalse($context->hasDefaultValue());
		self::assertSame($property->getName(), $context->getPropertyName());
		self::assertSame($fieldName, $context->getFieldName());

		self::assertEquals($typeCreator(), $context->getType());
		self::assertNotSame($typeCreator(), $context->getType());
		self::assertSame($context->getType(), $context->getType());
	}

}
