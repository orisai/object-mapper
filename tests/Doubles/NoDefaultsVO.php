<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Attributes\Expect\AnyOf;
use Orisai\ObjectMapper\Attributes\Expect\ArrayOf;
use Orisai\ObjectMapper\Attributes\Expect\IntValue;
use Orisai\ObjectMapper\Attributes\Expect\MappedObjectValue;
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\Attributes\Expect\NullValue;
use Orisai\ObjectMapper\Attributes\Expect\StringValue;
use Orisai\ObjectMapper\MappedObject;

final class NoDefaultsVO implements MappedObject
{

	/** @StringValue() */
	public string $string;

	/**
	 * @AnyOf({
	 *     @StringValue(),
	 *     @NullValue(),
	 * })
	 */
	public ?string $nullableString;

	/**
	 * @var array<mixed>
	 *
	 * @ArrayOf(
	 *     @MixedValue()
	 * )
	 */
	public array $arrayOfMixed;

	/** @MappedObjectValue(DefaultsVO::class) */
	public DefaultsVO $structure;

	/**
	 * @var array<int, DefaultsVO>
	 *
	 * @ArrayOf(
	 *     key=@IntValue(),
	 *     item=@MappedObjectValue(DefaultsVO::class),
	 * )
	 */
	public array $manyStructures;

}
