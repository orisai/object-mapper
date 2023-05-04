<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\ArrayOf;
use Orisai\ObjectMapper\Rules\IntValue;
use Orisai\ObjectMapper\Rules\MappedObjectValue;
use Orisai\ObjectMapper\Rules\MixedValue;
use Orisai\ObjectMapper\Rules\NullValue;
use Orisai\ObjectMapper\Rules\StringValue;

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
