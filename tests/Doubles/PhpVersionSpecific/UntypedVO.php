<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\PhpVersionSpecific;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\DefaultValue;
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\NullValue;
use Orisai\ObjectMapper\Rules\StringValue;

final class UntypedVO implements MappedObject
{

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
	 * @param null $null
	 * @param null $nullWithDefault
	 */
	public function __construct(
		?string $nullableString,
		$null,
		?string $nullableStringWithDefault = null,
		$nullWithDefault = null
	)
	{
		$this->nullableString = $nullableString;
		$this->null = $null;
		$this->nullableStringWithDefault = $nullableStringWithDefault;
		$this->nullWithDefault = $nullWithDefault;
	}

}
