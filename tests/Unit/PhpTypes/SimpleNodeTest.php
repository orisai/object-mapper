<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\PhpTypes;

use Generator;
use Orisai\ObjectMapper\PhpTypes\SimpleNode;
use PHPUnit\Framework\TestCase;

final class SimpleNodeTest extends TestCase
{

	/**
	 * @dataProvider provide
	 */
	public function test(SimpleNode $node, string $expected): void
	{
		self::assertSame($expected, (string) $node);
	}

	public function provide(): Generator
	{
		yield [
			new SimpleNode('int'),
			'int',
		];

		yield [
			new SimpleNode('int<min, 100>'),
			'int<min, 100>',
		];
	}

}
