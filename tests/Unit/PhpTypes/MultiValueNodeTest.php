<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\PhpTypes;

use Generator;
use Orisai\ObjectMapper\PhpTypes\MultiValueNode;
use Orisai\ObjectMapper\PhpTypes\SimpleNode;
use PHPUnit\Framework\TestCase;

final class MultiValueNodeTest extends TestCase
{

	/**
	 * @dataProvider provide
	 */
	public function test(MultiValueNode $node, string $expected): void
	{
		self::assertSame($expected, (string) $node);
	}

	public function provide(): Generator
	{
		yield [
			new MultiValueNode(
				'array',
				new SimpleNode('int'),
				new SimpleNode('string'),
			),
			'array<int, string>',
		];

		yield [
			new MultiValueNode(
				'list',
				null,
				new SimpleNode('string'),
			),
			'list<string>',
		];
	}

}
