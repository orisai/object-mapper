<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\Annotation\Expect\AnyOf;
use Orisai\ObjectMapper\Annotation\Expect\ArrayOf;
use Orisai\ObjectMapper\Annotation\Expect\MixedValue;
use Orisai\ObjectMapper\Annotation\Expect\Structure;
use Orisai\ObjectMapper\ValueObject;

final class StructuresVO extends ValueObject
{

	/** @Structure(DefaultsVO::class) */
	public DefaultsVO $structure;

	/**
	 * @var DefaultsVO|array<mixed>
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
	 * @ArrayOf(
	 *     @AnyOf({
	 *     	   @Structure(NoDefaultsVO::class),
	 *         @Structure(DefaultsVO::class),
	 *     })
	 * )
	 */
	public array $manyStructures;

}
