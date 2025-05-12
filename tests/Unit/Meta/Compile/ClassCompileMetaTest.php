<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Compile;

use Orisai\ObjectMapper\Callbacks\BeforeValidation;
use Orisai\ObjectMapper\Docs\Description;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Modifiers\FieldName;
use Orisai\ReflectionMeta\Structure\ClassStructure;
use Orisai\SourceMap\ClassSource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;

final class ClassCompileMetaTest extends TestCase
{

	public function test(): void
	{
		$definitions = [
			new BeforeValidation('foo'),
			new Description('description'),
			new FieldName('foo'),
		];
		$reflector = new ReflectionClass(NoDefaultsVO::class);
		$class = new ClassStructure(
			$reflector,
			new ClassSource($reflector),
		);

		$meta = new ClassCompileMeta($definitions, $class);

		self::assertSame(
			$definitions,
			$meta->getDefinitions(),
		);
		self::assertSame(
			$class,
			$meta->getClass(),
		);
	}

}
