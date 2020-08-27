<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

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
	 * @AnyOf(
	 *     @StringValue(),
	 *     @NullValue(),
	 * )
	 */
	public ?string $nullableString;

	/** @StringValue() */
	public string $untypedString;

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
	 *     keyRule=@IntValue(),
	 *     itemRule=@Structure(DefaultsVO::class),
	 * )
	 */
	public array $manyStructures;

}
