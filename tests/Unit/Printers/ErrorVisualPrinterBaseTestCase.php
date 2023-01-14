<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Unit\Printers;

use Orisai\ObjectMapper\Printers\ErrorVisualPrinter;
use Orisai\ObjectMapper\Printers\TypeToPrimitiveConverter;
use Orisai\ObjectMapper\Types\ArrayType;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\EnumType;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\MessageType;
use Orisai\ObjectMapper\Types\SimpleValueType;
use PHPUnit\Framework\TestCase;

/**
 * @template TConverter of TypeToPrimitiveConverter
 */
abstract class ErrorVisualPrinterBaseTestCase extends TestCase
{

	/** @phpstan-var TConverter */
	protected TypeToPrimitiveConverter $converter;

	protected ErrorVisualPrinter $printer;

	abstract public function testMessage(MessageType $type): void;

	abstract public function testSimpleValue(SimpleValueType $type): void;

	abstract public function testSimpleTypeWithParameters(SimpleValueType $type): void;

	abstract public function testSimpleTypeWithInvalidParameters(SimpleValueType $type): void;

	abstract public function testEnum(EnumType $type): void;

	abstract public function testArray(ArrayType $type): void;

	abstract public function testArrayInvalid(ArrayType $type): void;

	abstract public function testArraySimpleInvalid(ArrayType $type): void;

	abstract public function testArraySimpleInvalidWithParameters(ArrayType $type): void;

	abstract public function testArrayTypeCompoundInvalid(ArrayType $type): void;

	abstract public function testArrayTypeSimpleInvalidWithInvalidParameters(ArrayType $type): void;

	abstract public function testArrayTypeInvalidPairs(ArrayType $type): void;

	abstract public function testListType(ArrayType $type): void;

	abstract public function testListTypeInvalid(ArrayType $type): void;

	abstract public function testListTypeInvalidWithParameter(ArrayType $type): void;

	abstract public function testListTypeInvalidWithInvalidParameter(ArrayType $type): void;

	abstract public function testListTypeWithInvalidValues(ArrayType $type): void;

	abstract public function testCompoundTypeOverwriteSubtype(CompoundType $type): void;

	abstract public function testCompoundTypeOverwriteSubtypeComplex(CompoundType $type): void;

	abstract public function testMappedObjectType(MappedObjectType $type): void;

	abstract public function testMappedObjectTypeInvalid(MappedObjectType $type): void;

	abstract public function testMappedObjectTypeInvalidWithInvalidFields(MappedObjectType $type): void;

}
