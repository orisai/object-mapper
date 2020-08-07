<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\ObjectMapper\Annotation\Expect;
use Orisai\ObjectMapper\ValueObject;

final class NoDefaultsVO extends ValueObject
{

	/** @Expect\StringValue() */
	public string $string;

	/**
	 * @Expect\AnyOf(
	 *     @Expect\StringValue(),
	 *     @Expect\NullValue(),
	 * )
	 */
	public ?string $nullableString;

	/** @Expect\StringValue() */
	public string $untypedString;

	/**
	 * @var array<mixed>
	 * @Expect\ArrayOf(
	 *     @Expect\MixedValue()
	 * )
	 */
	public array $arrayOfMixed;

	/** @Expect\Structure(DefaultsVO::class) */
	public DefaultsVO $structure;

	/**
	 * @var array<int, DefaultsVO>
	 * @Expect\ArrayOf(
	 *     keyType=@Expect\IntValue(),
	 *     itemType=@Expect\Structure(DefaultsVO::class),
	 * )
	 */
	public array $manyStructures;

}
