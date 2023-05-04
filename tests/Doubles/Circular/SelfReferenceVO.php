<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Circular;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\MappedObjectValue;
use Orisai\ObjectMapper\Rules\NullValue;
use Orisai\ObjectMapper\Rules\StringValue;

final class SelfReferenceVO implements MappedObject
{

	/**
	 * @AnyOf({
	 *     @MappedObjectValue(SelfReferenceVO::class),
	 *     @NullValue(),
	 * })
	 */
	public ?self $selfOrNull;

	/** @StringValue() */
	public string $another;

}
