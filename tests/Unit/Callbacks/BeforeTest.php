<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Callbacks;

use Orisai\ObjectMapper\Callbacks\Before;
use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class BeforeTest extends TestCase
{

	public function test(): void
	{
		$method = 'methodName';
		$definition = new Before($method);

		self::assertSame(BeforeCallback::class, $definition->getType());
		self::assertSame(
			[
				'method' => $method,
				'runtime' => 'process',
			],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsCallbackAnnotation(get_class($definition));
		if (PHP_VERSION_ID >= 8_00_00) {
			DefinitionTester::assertIsCallbackAttribute(get_class($definition));
		}
	}

}
