<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Compile;

use Orisai\ObjectMapper\Meta\Compile\ModifierCompileMeta;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use PHPUnit\Framework\TestCase;

final class ModifierCompileMetaTest extends TestCase
{

	public function test(): void
	{
		$type = FieldNameModifier::class;
		$args = ['foo' => 'bar'];
		$meta = new ModifierCompileMeta($type, $args);

		self::assertSame($type, $meta->getType());
		self::assertSame($args, $meta->getArgs());
	}

}
