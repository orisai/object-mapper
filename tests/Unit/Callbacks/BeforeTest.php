<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Callbacks;

use Generator;
use Orisai\ObjectMapper\Callbacks\Before;
use Orisai\ObjectMapper\Callbacks\BeforeCallback;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
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

	/**
	 * @param key-of<CallbackRuntime::ValuesAndNames> $runtime
	 *
	 * @dataProvider provideVariant
	 */
	public function testVariant(string $method, string $runtime): void
	{
		$definition = new Before($method, $runtime);

		self::assertEquals(
			[
				'method' => $method,
				'runtime' => $runtime,
			],
			$definition->getArgs(),
		);
	}

	public static function provideVariant(): Generator
	{
		yield [
			'a',
			CallbackRuntime::Process,
		];

		yield [
			'b',
			CallbackRuntime::Always,
		];
	}

}
