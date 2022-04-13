<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Attributes\Expect\AnyOf;
use Orisai\ObjectMapper\Attributes\Expect\ArrayOf;
use Orisai\ObjectMapper\Attributes\Expect\MappedObjectValue;
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\MappedObject;

final class StructuresVO extends MappedObject
{

	/** @MappedObjectValue(DefaultsVO::class) */
	public DefaultsVO $structure;

	/**
	 * @var DefaultsVO|array<mixed>
	 *
	 * @AnyOf({
	 *     @MappedObjectValue(DefaultsVO::class),
	 *     @ArrayOf(
	 *          @MixedValue()
	 *     )
	 * })
	 */
	public $structureOrArray;

	/**
	 * @var DefaultsVO|array<mixed>
	 *
	 * @AnyOf({
	 *     @MappedObjectValue(DefaultsVO::class),
	 *     @ArrayOf(
	 *          @MixedValue()
	 *     )
	 * })
	 */
	public $anotherStructureOrArray;

	/**
	 * @var array<DefaultsVO|NoDefaultsVO>
	 *
	 * @ArrayOf(
	 *     @AnyOf({
	 *         @MappedObjectValue(NoDefaultsVO::class),
	 *         @MappedObjectValue(DefaultsVO::class),
	 *     })
	 * )
	 */
	public array $manyStructures;

}
