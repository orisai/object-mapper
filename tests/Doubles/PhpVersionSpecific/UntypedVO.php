<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific;

use Orisai\ObjectMapper\Attributes\Modifiers\DefaultValue;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\NullValue;
use Orisai\ObjectMapper\Rules\StringValue;

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
