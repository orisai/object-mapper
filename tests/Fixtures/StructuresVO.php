<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\ObjectMapper\Annotation\Expect;
use Orisai\ObjectMapper\ValueObject;

final class StructuresVO extends ValueObject
{

	/** @Expect\Structure(DefaultsVO::class) */
	public DefaultsVO $structure;

	/**
	 * @var DefaultsVO|array<mixed>
	 * @Expect\AnyOf(
	 *     @Expect\Structure(DefaultsVO::class),
	 *     @Expect\ArrayOf(
	 *          @Expect\MixedValue()
	 *     )
	 * )
	 */
	public $structureOrArray;

	/**
	 * @var DefaultsVO|array<mixed>
	 * @Expect\AnyOf(
	 *     @Expect\Structure(DefaultsVO::class),
	 *     @Expect\ArrayOf(
	 *          @Expect\MixedValue()
	 *     )
	 * )
	 */
	public $anotherStructureOrArray;

	/**
	 * @var array<DefaultsVO|NoDefaultsVO>
	 * @Expect\ArrayOf(
	 *     @Expect\AnyOf(
	 *     	   @Expect\Structure(NoDefaultsVO::class),
	 *         @Expect\Structure(DefaultsVO::class),
	 *     )
	 * )
	 */
	public array $manyStructures;

}
