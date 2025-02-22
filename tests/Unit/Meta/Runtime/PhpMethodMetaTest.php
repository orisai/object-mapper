<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Runtime;

use Generator;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Runtime\PhpMethodMeta;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;

final class PhpMethodMetaTest extends TestCase
{

	/**
	 * @param class-string<MappedObject> $declaringClass
	 *
	 * @dataProvider provide
	 */
	public function test(
		string $declaringClass,
		string $method,
		bool $isPublic,
		bool $isStatic,
		bool $returnsValue
	): void
	{
		$meta = new PhpMethodMeta(
			$declaringClass,
			$method,
			$isPublic,
			$isStatic,
			$returnsValue,
		);

		self::assertSame($declaringClass, $meta->declaringClass);
		self::assertSame($method, $meta->method);
		self::assertSame($isPublic, $meta->isPublic);
		self::assertSame($isStatic, $meta->isStatic);
		self::assertSame($returnsValue, $meta->returnsValue);
	}

	public function provide(): Generator
	{
		yield [
			NoDefaultsVO::class,
			'method',
			true,
			false,
			false,
		];

		yield [
			DefaultsVO::class,
			'anotherMethod',
			false,
			true,
			false,
		];

		yield [
			DefaultsVO::class,
			'anotherMethod',
			false,
			false,
			true,
		];
	}

}
