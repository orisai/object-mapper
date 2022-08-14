<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\PhpTypes;

use Generator;
use Orisai\ObjectMapper\PhpTypes\LiteralNode;
use PHPUnit\Framework\TestCase;

final class LiteralNodeTest extends TestCase
{

	/**
	 * @dataProvider provide
	 */
	public function test(LiteralNode $node, string $expected): void
	{
		self::assertSame($expected, (string) $node);
	}

	public function provide(): Generator
	{
		yield [
			new LiteralNode(123),
			'123',
		];

		yield [
			new LiteralNode(123.456),
			'123.456',
		];

		yield [
			new LiteralNode(true),
			'true',
		];

		yield [
			new LiteralNode(false),
			'false',
		];

		yield [
			new LiteralNode(null),
			'null',
		];

		yield [
			new LiteralNode('string'),
			"'string'",
		];

		yield [
			new LiteralNode(''),
			"''",
		];
	}

}
