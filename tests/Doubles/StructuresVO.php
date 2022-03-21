<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Attributes\Expect\AnyOf;
use Orisai\ObjectMapper\Attributes\Expect\ArrayOf;
use Orisai\ObjectMapper\Attributes\Expect\MixedValue;
use Orisai\ObjectMapper\Attributes\Expect\Structure;
use Orisai\ObjectMapper\MappedObject;

final class StructuresVO extends MappedObject
{

	/** @Structure(DefaultsVO::class) */
	public DefaultsVO $structure;

	/**
	 * @var DefaultsVO|array<mixed>
	 *
	 * @AnyOf({
	 *     @Structure(DefaultsVO::class),
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
	 *     @Structure(DefaultsVO::class),
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
	 *         @Structure(NoDefaultsVO::class),
	 *         @Structure(DefaultsVO::class),
	 *     })
	 * )
	 */
	public array $manyStructures;

}
