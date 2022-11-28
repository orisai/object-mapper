<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\PhpTypes;

use Generator;
use Orisai\ObjectMapper\PhpTypes\CompoundNode;
use Orisai\ObjectMapper\PhpTypes\SimpleNode;
use PHPUnit\Framework\TestCase;

final class CompoundNodeTest extends TestCase
{

	/**
	 * @dataProvider provide
	 */
	public function test(CompoundNode $node, string $expected): void
	{
		self::assertSame($expected, (string) $node);
	}

	public function provide(): Generator
	{
		yield [
			CompoundNode::createOrType([
				new SimpleNode('int'),
				new SimpleNode('string'),
			]),
			'(int|string)',
		];

		yield [
			CompoundNode::createAndType([
				new SimpleNode('int'),
				new SimpleNode('string'),
			]),
			'(int&string)',
		];

		yield [
			CompoundNode::createAndType([
				new SimpleNode('int'),
			]),
			'(int)',
		];

		yield [
			CompoundNode::createAndType([]),
			'()',
		];
	}

}
