<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Meta;

use Orisai\Exceptions\Logic\InvalidState;
use Tests\Orisai\ObjectMapper\Fixtures\FieldNameIdenticalWithAnotherPropertyNameVO;
use Tests\Orisai\ObjectMapper\Fixtures\MultipleIdenticalFieldNamesVO;
use Tests\Orisai\ObjectMapper\Toolkit\ProcessingTestCase;

final class MetaLoaderTest extends ProcessingTestCase
{

	public function testMultipleIdenticalFieldNames(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(<<<'TXT'
Context: Trying to define field name for mapped property of
         `Tests\Orisai\ObjectMapper\Fixtures\MultipleIdenticalFieldNamesVO`.
Problem: Field name `field` is identical for properties `property1, property2`.
Solution: Define unique field name for each mapped property.
TXT);

		$this->metaLoader->load(MultipleIdenticalFieldNamesVO::class);
	}

	public function testFieldNameIdenticalWithAnotherPropertyName(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(<<<'TXT'
Context: Trying to define field name for mapped property of
         `Tests\Orisai\ObjectMapper\Fixtures\FieldNameIdenticalWithAnotherPropertyNameVO`.
Problem: Field name `field` defined by property `property` collides with
         property `field` which does not have a field name.
Solution: Rename field of property `property` or rename property `field` or give
          it a unique field name.
TXT);

		$this->metaLoader->load(FieldNameIdenticalWithAnotherPropertyNameVO::class);
	}

}
