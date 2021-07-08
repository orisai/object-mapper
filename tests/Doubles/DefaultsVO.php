<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Annotation\Expect\AnyOf;
use Orisai\ObjectMapper\Annotation\Expect\ArrayOf;
use Orisai\ObjectMapper\Annotation\Expect\MixedValue;
use Orisai\ObjectMapper\Annotation\Expect\NullValue;
use Orisai\ObjectMapper\Annotation\Expect\StringValue;
use Orisai\ObjectMapper\ValueObject;

final class DefaultsVO extends ValueObject
{

	/** @StringValue() */
	public string $string = 'foo';

	/**
	 * @AnyOf(
	 *     @StringValue(),
	 *     @NullValue(),
	 * )
	 */
	public ?string $nullableString = null;

	/**
	 * Is optional because without type there's no difference between uninitialized and null value ($foo; and $foo = null; are the same)
	 *
	 * @AnyOf(
	 *     @StringValue(),
	 *     @NullValue(),
	 * )
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
	 */
	public $untypedNullableString;

	/**
	 * Is optional because without type there's no difference between uninitialized and null value ($foo; and $foo = null; are the same)
	 *
	 * @var null
	 * @NullValue()
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
	 */
	public $untypedNull;

	/**
	 * @var array<string>
	 * @ArrayOf(
	 *     @MixedValue()
	 * )
	 */
	public array $arrayOfMixed = [
		0 => 'foo',
		'bar' => 'baz',
	];

}
