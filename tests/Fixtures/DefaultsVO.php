<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\ObjectMapper\Annotation\Expect;
use Orisai\ObjectMapper\ValueObject;

final class DefaultsVO extends ValueObject
{

	/** @Expect\StringValue() */
	public string $string = 'foo';

	/**
	 * @Expect\AnyOf(
	 *     @Expect\StringValue(),
	 *     @Expect\NullValue(),
	 * )
	 */
	public ?string $nullableString = null;

	/**
	 * Is optional because without type there's no difference between uninitialized and null value ($foo; and $foo = null; are the same)
	 *
	 * @Expect\AnyOf(
	 *     @Expect\StringValue(),
	 *     @Expect\NullValue(),
	 * )
	 */
	public ?string $untypedNullableString = null;

	/**
	 * Is optional because without type there's no difference between uninitialized and null value ($foo; and $foo = null; are the same)
	 *
	 * @var null
	 * @Expect\NullValue()
	 */
	public $untypedNull;

	/**
	 * phpcs:disable Squiz.Arrays.ArrayDeclaration.KeySpecified
	 * @var array<string>
	 * @Expect\ArrayOf(
	 *     @Expect\MixedValue()
	 * )
	 */
	public array $arrayOfMixed = [
		'foo',
		'bar' => 'baz',
	];

}
