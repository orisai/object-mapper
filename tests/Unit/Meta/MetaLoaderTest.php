<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta;

use Orisai\Exceptions\Logic\InvalidState;
use Tests\Orisai\ObjectMapper\Doubles\FieldNames\FieldNameIdenticalWithAnotherPropertyNameVO;
use Tests\Orisai\ObjectMapper\Doubles\FieldNames\MultipleIdenticalFieldNamesVO;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class MetaLoaderTest extends ProcessingTestCase
{

	public function testMultipleIdenticalFieldNames(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(<<<'TXT'
Context: Validating mapped property
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\MultipleIdenticalFieldNamesVO::$property2'.
Problem: Field name 'field' defined in field name meta collides with field name
         of property
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\MultipleIdenticalFieldNamesVO::$property1'
         defined in field name meta.
Solution: Define unique field name for each mapped property.
TXT);

		$this->metaLoader->load(MultipleIdenticalFieldNamesVO::class);
	}

	public function testFieldNameIdenticalWithAnotherPropertyName(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(<<<'TXT'
Context: Validating mapped property
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\FieldNameIdenticalWithAnotherPropertyNameVO::$property'.
Problem: Field name 'field' defined in field name meta collides with field name
         of property
         'Tests\Orisai\ObjectMapper\Doubles\FieldNames\FieldNameIdenticalWithAnotherPropertyNameVO::$field'
         defined in property name.
Solution: Define unique field name for each mapped property.
TXT);

		$this->metaLoader->load(FieldNameIdenticalWithAnotherPropertyNameVO::class);
	}

}
