<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Runtime;

use Generator;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Runtime\PhpPropertyMeta;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\ObjectMapper\Doubles\DefaultsVO;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;

final class PhpPropertyMetaTest extends TestCase
{

	/**
	 * @param class-string<MappedObject> $declaringClass
	 *
	 * @dataProvider provide
	 */
	public function test(
		string $declaringClass,
		string $name,
		bool $isPublicSet
	): void
	{
		$meta = new PhpPropertyMeta($declaringClass, $name, $isPublicSet);

		self::assertSame($declaringClass, $meta->declaringClass);
		self::assertSame($name, $meta->name);
		self::assertSame($isPublicSet, $meta->isPublicSet);
	}

	public function provide(): Generator
	{
		yield [
			NoDefaultsVO::class,
			'property',
			true,
		];

		yield [
			DefaultsVO::class,
			'anotherProperty',
			false,
		];
	}

}
