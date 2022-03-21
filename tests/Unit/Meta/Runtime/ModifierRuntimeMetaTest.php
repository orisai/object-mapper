<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Runtime;

use Orisai\ObjectMapper\Meta\Runtime\ModifierRuntimeMeta;
use Orisai\ObjectMapper\Modifiers\FieldNameArgs;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use PHPUnit\Framework\TestCase;

final class ModifierRuntimeMetaTest extends TestCase
{

	public function test(): void
	{
		$type = FieldNameModifier::class;
		$args = new FieldNameArgs('name');
		$meta = new ModifierRuntimeMeta($type, $args);

		self::assertSame($type, $meta->getType());
		self::assertSame($args, $meta->getArgs());
	}

}
