<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Callbacks;

use Orisai\ObjectMapper\Callbacks\AfterMapping;
use Orisai\ObjectMapper\Callbacks\AfterMappingCallback;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class AfterMappingTest extends TestCase
{

	public function test(): void
	{
		$method = 'methodName';
		$definition = new AfterMapping($method);

		self::assertSame(AfterMappingCallback::class, $definition->getType());
		self::assertSame(
			[
				AfterMappingCallback::Method => $method,
			],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsMappingCallbackAnnotation(get_class($definition));
		if (PHP_VERSION_ID >= 8_00_00) {
			DefinitionTester::assertIsMappingCallbackAttribute(get_class($definition));
		}
	}

}
