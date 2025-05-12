<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Scope;

use Orisai\ObjectMapper\Meta\Scope\HierarchyPosition;
use PHPUnit\Framework\TestCase;

final class HierarchyPositionTest extends TestCase
{

	public function test(): void
	{
		self::assertSame('Anywhere', HierarchyPosition::anywhere()->name);
		self::assertSame('FirstType', HierarchyPosition::firstType()->name);

		self::assertSame(
			[
				HierarchyPosition::anywhere(),
				HierarchyPosition::firstType(),
			],
			HierarchyPosition::cases(),
		);
	}

}
