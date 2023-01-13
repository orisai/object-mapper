<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Runtime;

use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Meta\DefaultValueMeta;
use Orisai\ObjectMapper\Meta\Runtime\ClassRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\PropertyRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;
use Orisai\ObjectMapper\Rules\MixedRule;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class RuntimeMetaTest extends TestCase
{

	public function test(): void
	{
		$class = new ClassRuntimeMeta([], [], []);
		$properties = [
			'a' => new PropertyRuntimeMeta(
				[],
				[],
				[],
				new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
				DefaultValueMeta::fromNothing(),
				new ReflectionClass(self::class),
			),
		];
		$map = ['field' => 'property'];

		$meta = new RuntimeMeta($class, $properties, $map);

		self::assertSame(
			$class,
			$meta->getClass(),
		);
		self::assertSame(
			$properties,
			$meta->getProperties(),
		);
		self::assertSame(
			$map,
			$meta->getFieldsPropertiesMap(),
		);
	}

}
