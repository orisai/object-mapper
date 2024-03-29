<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Printers;

use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\EnumType;
use Orisai\ObjectMapper\Types\GenericArrayType;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use PHPUnit\Framework\TestCase;

abstract class ErrorVisualPrinterBaseTestCase extends TestCase
{

	abstract public function testUnsupportedType(): void;

	abstract public function testMessage(MessageType $type): void;

	abstract public function testSimpleValue(SimpleValueType $type): void;

	abstract public function testSimpleTypeWithParameters(SimpleValueType $type): void;

	abstract public function testSimpleTypeWithInvalidParameters(SimpleValueType $type): void;

	abstract public function testEnum(EnumType $type): void;

	abstract public function testArray(GenericArrayType $type): void;

	abstract public function testArrayInvalid(GenericArrayType $type): void;

	abstract public function testArraySimpleInvalid(GenericArrayType $type): void;

	abstract public function testArraySimpleInvalidWithParameters(GenericArrayType $type): void;

	abstract public function testArrayTypeCompoundInvalid(GenericArrayType $type): void;

	abstract public function testArrayTypeSimpleInvalidWithInvalidParameters(GenericArrayType $type): void;

	abstract public function testArrayTypeInvalidPairs(GenericArrayType $type): void;

	abstract public function testListType(GenericArrayType $type): void;

	abstract public function testListTypeInvalid(GenericArrayType $type): void;

	abstract public function testListTypeInvalidWithParameter(GenericArrayType $type): void;

	abstract public function testListTypeInvalidWithInvalidParameter(GenericArrayType $type): void;

	abstract public function testListTypeWithInvalidValues(GenericArrayType $type): void;

	abstract public function testCompoundTypeOverwriteSubtype(CompoundType $type): void;

	abstract public function testCompoundTypeOverwriteSubtypeComplex(CompoundType $type): void;

	abstract public function testMappedObjectType(MappedObjectType $type): void;

	abstract public function testMappedObjectTypeInvalid(MappedObjectType $type): void;

	abstract public function testMappedObjectTypeInvalidWithInvalidFields(MappedObjectType $type): void;

}
