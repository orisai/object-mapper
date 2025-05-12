<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Scope;

use Orisai\ObjectMapper\Meta\Scope\HierarchyPosition;
use Orisai\ObjectMapper\Meta\Scope\RepeatableBehavior;
use Orisai\ObjectMapper\Meta\Scope\ScopeConfig;
use Orisai\ObjectMapper\Meta\Scope\Target;
use PHPUnit\Framework\TestCase;

final class ScopeConfigTest extends TestCase
{

	public function test(): void
	{
		$config = new ScopeConfig(
			true,
			RepeatableBehavior::merge(),
			HierarchyPosition::firstType(),
			[Target::targetClass()],
		);

		self::assertTrue($config->required);
		self::assertSame(RepeatableBehavior::merge(), $config->repeatableBehavior);
		self::assertSame(HierarchyPosition::firstType(), $config->hierarchyPosition);
		self::assertSame([Target::targetClass()], $config->targets);
	}

	public function testVariant(): void
	{
		$config = new ScopeConfig(
			false,
			RepeatableBehavior::override(),
			HierarchyPosition::anywhere(),
			[Target::targetClass(), Target::targetProperty()],
		);

		self::assertFalse($config->required);
		self::assertSame(RepeatableBehavior::override(), $config->repeatableBehavior);
		self::assertSame(HierarchyPosition::anywhere(), $config->hierarchyPosition);
		self::assertSame([Target::targetClass(), Target::targetProperty()], $config->targets);
	}

}
