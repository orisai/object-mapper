<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Attributes\Expect\AnyOf;
use Orisai\ObjectMapper\Attributes\Expect\ArrayOf;
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class DefaultsVO implements MappedObject
{

	/** @StringValue() */
	public string $string = 'foo';

	/**
	 * @AnyOf({
	 *     @StringValue(),
	 *     @NullValue(),
	 * })
	 */
	public ?string $nullableString = null;

	/**
	 * Is optional because without type there's no difference between uninitialized and null value ($foo; and $foo = null; are the same)
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
	 *
	 * @AnyOf({
	 *     @StringValue(),
	 *     @NullValue(),
	 * })
	 */
	public $untypedNullableString;

	/**
	 * Is optional because without type there's no difference between uninitialized and null value ($foo; and $foo = null; are the same)
	 *
	 * @var null
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
	 *
	 * @NullValue()
	 */
	public $untypedNull;

	/**
	 * @var array<string>
	 *
	 * @ArrayOf(
	 *     @MixedValue()
	 * )
	 */
	public array $arrayOfMixed = [
		0 => 'foo',
		'bar' => 'baz',
	];

}
