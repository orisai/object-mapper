<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Annotation\Expect\AnyOf;
use Orisai\ObjectMapper\Annotation\Expect\ArrayOf;
use Orisai\ObjectMapper\Annotation\Expect\IntValue;
use Orisai\ObjectMapper\Annotation\Expect\MixedValue;
use Orisai\ObjectMapper\Annotation\Expect\NullValue;
use Orisai\ObjectMapper\Annotation\Expect\StringValue;
use Orisai\ObjectMapper\Annotation\Expect\Structure;
use Orisai\ObjectMapper\ValueObject;

final class NoDefaultsVO extends ValueObject
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
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
	 * @StringValue()
	 */
	public $untypedString;

	/**
	 * @var array<mixed>
	 * @ArrayOf(
	 *     @MixedValue()
	 * )
	 */
	public array $arrayOfMixed;

	/** @Structure(DefaultsVO::class) */
	public DefaultsVO $structure;

	/**
	 * @var array<int, DefaultsVO>
	 * @ArrayOf(
	 *     key=@IntValue(),
	 *     item=@Structure(DefaultsVO::class),
	 * )
	 */
	public array $manyStructures;

}
