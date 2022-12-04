<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Attributes\Expect\AnyOf;
use Orisai\ObjectMapper\Attributes\Expect\MappedObjectValue;
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\MappedObject;

final class SelfReferenceVO extends MappedObject
{

	/**
	 * @AnyOf({
	 *     @MappedObjectValue(SelfReferenceVO::class),
	 *     @NullValue(),
	 * })
	 */
	public ?SelfReferenceVO $selfOrNull;

}
