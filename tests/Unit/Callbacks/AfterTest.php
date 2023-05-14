<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Callbacks;

use Orisai\ObjectMapper\Callbacks\After;
use Orisai\ObjectMapper\Callbacks\AfterCallback;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class AfterTest extends TestCase
{

	public function test(): void
	{
		$method = 'methodName';
		$definition = new After($method);

		self::assertSame(AfterCallback::class, $definition->getType());
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
