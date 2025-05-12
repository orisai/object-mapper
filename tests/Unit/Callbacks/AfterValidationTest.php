<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Callbacks;

use Generator;
use Orisai\ObjectMapper\Callbacks\AfterValidation;
use Orisai\ObjectMapper\Callbacks\AfterValidationCallback;
use Orisai\ObjectMapper\Callbacks\CallbackRuntime;
use Orisai\ObjectMapper\Tester\DefinitionTester;
use PHPUnit\Framework\TestCase;
use function get_class;
use const PHP_VERSION_ID;

final class AfterValidationTest extends TestCase
{

	public function test(): void
	{
		$method = 'methodName';
		$definition = new AfterValidation($method);

		self::assertSame(AfterValidationCallback::class, $definition->getHandler());
		self::assertSame(
			[
				AfterValidationCallback::Method => $method,
				AfterValidationCallback::Runtime => 'process',
			],
			$definition->getArgs(),
		);

		DefinitionTester::assertIsValidationCallbackAnnotation(get_class($definition));
		if (PHP_VERSION_ID >= 8_00_00) {
			DefinitionTester::assertIsValidationCallbackAttribute(get_class($definition));
		}
	}

	/**
	 * @param key-of<CallbackRuntime::ValuesAndNames> $runtime
	 *
	 * @dataProvider provideVariant
	 */
	public function testVariant(string $method, string $runtime): void
	{
		$definition = new AfterValidation($method, $runtime);

		self::assertEquals(
			[
				AfterValidationCallback::Method => $method,
				AfterValidationCallback::Runtime => $runtime,
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
