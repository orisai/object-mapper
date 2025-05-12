<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta\Compile;

use Orisai\ObjectMapper\Callbacks\BeforeValidation;
use Orisai\ObjectMapper\Docs\Description;
use Orisai\ObjectMapper\Meta\Compile\FieldCompileMeta;
use Orisai\ObjectMapper\Modifiers\FieldName;
use Orisai\ObjectMapper\Rules\MixedValue;
use Orisai\ReflectionMeta\Structure\PropertyStructure;
use Orisai\SourceMap\PropertySource;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Tests\Orisai\ObjectMapper\Doubles\NoDefaultsVO;

final class FieldCompileMetaTest extends TestCase
{

	public function test(): void
	{
		$definitions = [
			new BeforeValidation('foo'),
			new Description('description'),
			new FieldName('foo'),
			new MixedValue(),
		];
		$reflector = new ReflectionProperty(NoDefaultsVO::class, 'string');
		$property = new PropertyStructure(
			$reflector,
			new PropertySource($reflector),
			[],
		);

		$meta = new FieldCompileMeta($definitions, $property);

		self::assertSame(
			$definitions,
			$meta->getDefinitions(),
		);
		self::assertSame(
			$property,
			$meta->getPropertyStructure(),
		);
	}

}
