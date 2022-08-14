<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\PhpTypes;

use Generator;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\PhpTypes\ClassReferenceNode;
use Orisai\ObjectMapper\PhpTypes\LiteralNode;
use Orisai\ObjectMapper\PhpTypes\MultiValueNode;
use Orisai\ObjectMapper\PhpTypes\SimpleNode;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ClassReferenceNodeTest extends TestCase
{

	/**
	 * @dataProvider provide
	 */
	public function test(ClassReferenceNode $node, string $class, string $shape): void
	{
		self::assertSame($class, (string) $node);
		self::assertSame($shape, $node->getArrayShape());
	}

	public function provide(): Generator
	{
		yield [
			new ClassReferenceNode(stdClass::class, []),
			stdClass::class,
			'array{}',
		];

		yield [
			new ClassReferenceNode(MappedObject::class, [
				0 => new MultiValueNode('array', new SimpleNode('string'), new SimpleNode('int')),
			]),
			MappedObject::class,
			'array{0: array<string, int>}',
		];

		yield [
			new ClassReferenceNode(MappedObject::class, [
				0 => new SimpleNode('int'),
				'key' => new SimpleNode('string'),
				2 => new LiteralNode(true),
				3 => new LiteralNode(''),
			]),
			MappedObject::class,
			"array{0: int, key: string, 2: true, 3: ''}",
		];
	}

}
