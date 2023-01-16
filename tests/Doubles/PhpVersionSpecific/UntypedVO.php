<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific;

use Orisai\ObjectMapper\Attributes\Expect\AnyOf;
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\Attributes\Modifiers\DefaultValue;
use Orisai\ObjectMapper\MappedObject;

final class UntypedVO implements MappedObject
{

	/**
	 * @var string|null
	 *
	 * @DefaultValue(value=null)
	 * @AnyOf({
	 *     @StringValue(),
	 *     @NullValue(),
	 * })
	 */
	public $nullableStringWithDefault;

	/**
	 * @var null
	 *
	 * @DefaultValue(value=null)
	 * @NullValue()
	 */
	public $nullWithDefault;

	/**
	 * @var string|null
	 *
	 * @AnyOf({
	 *     @StringValue(),
	 *     @NullValue(),
	 * })
	 */
	public $nullableString;

	/**
	 * @var null
	 *
	 * @NullValue()
	 */
	public $null;

}
