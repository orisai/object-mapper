<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Context;

use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Rules\StringRule;
use Orisai\ObjectMapper\Tester\ObjectMapperTester;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;
use Throwable;

final class TypeContextTest extends TestCase
{

	public function testDependencies(): void
	{
		$deps = (new ObjectMapperTester())->buildDependencies();
		$options = new Options();

		$context = new TypeContext($deps->metaLoader, $deps->ruleManager, $options);

		try {
			$context->getMeta(DefaultsVO::class);
		} catch (Throwable $e) {
			// Handled bellow
		}

		self::assertFalse(isset($e));

		try {
			$context->getRule(StringRule::class);
		} catch (Throwable $e) {
			// Handled bellow
		}

		self::assertFalse(isset($e));
	}

	public function testClone(): void
	{
		$deps = (new ObjectMapperTester())->buildDependencies();
		$options = new Options();

		$initContext = new TypeContext($deps->metaLoader, $deps->ruleManager, $options);
		$context = $initContext->createClone();

		self::assertEquals($initContext, $context);
		self::assertNotSame($initContext, $context);
	}

	public function testOptions(): void
	{
		$deps = (new ObjectMapperTester())->buildDependencies();
		$options = new Options();

		$initContext = new TypeContext($deps->metaLoader, $deps->ruleManager, $options);

		self::assertSame($options, $initContext->getOptions());

		$context = $initContext->createClone();
		self::assertSame($options, $initContext->getOptions());
		self::assertEquals($options, $context->getOptions());
		self::assertNotSame($options, $context->getOptions());
	}

	public function testProcessedClasses(): void
	{
		$deps = (new ObjectMapperTester())->buildDependencies();
		$options = new Options();

		$initContext = new TypeContext($deps->metaLoader, $deps->ruleManager, $options);

		self::assertSame([], $initContext->getProcessedClasses());

		$context = $initContext
			->withProcessedClass(DefaultsVO::class)
			->withProcessedClass(NoDefaultsVO::class);

		self::assertSame([], $initContext->getProcessedClasses());
		self::assertSame(
			[
				DefaultsVO::class,
				NoDefaultsVO::class,
			],
			$context->getProcessedClasses(),
		);
	}

}
