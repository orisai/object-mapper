<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Runtime;

use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Meta\Runtime\ClassRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\FieldRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuleRuntimeMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;
use Orisai\ObjectMapper\Meta\Shared\DefaultValueMeta;
use Orisai\ObjectMapper\Rules\MixedRule;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;
use function serialize;
use function unserialize;

final class RuntimeMetaTest extends TestCase
{

	public function test(): void
	{
		$class = new ClassRuntimeMeta([], [], []);
		$fields = [
			'a' => new FieldRuntimeMeta(
				[],
				[],
				[],
				new RuleRuntimeMeta(MixedRule::class, new EmptyArgs()),
				DefaultValueMeta::fromNothing(),
				new ReflectionProperty(NoDefaultsVO::class, 'string'),
			),
		];

		$meta = new RuntimeMeta($class, $fields);

		self::assertSame(
			$class,
			$meta->getClass(),
		);
		self::assertSame(
			$fields,
			$meta->getFields(),
		);
		self::assertEquals($meta, unserialize(serialize($meta)));
	}

}
