<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Rules\AnyOf;
use Orisai\ObjectMapper\Rules\ArrayOf;
use Orisai\ObjectMapper\Rules\MappedObjectValue;
use Orisai\ObjectMapper\Rules\MixedValue;

final class StructuresVO implements MappedObject
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

	/**
	 * @param DefaultsVO|array<mixed> $structureOrArray
	 * @param DefaultsVO|array<mixed> $anotherStructureOrArray
	 * @param array<DefaultsVO|NoDefaultsVO> $manyStructures
	 */
	public function __construct(
		DefaultsVO $structure,
		$structureOrArray,
		$anotherStructureOrArray,
		array $manyStructures
	)
	{
		$this->structure = $structure;
		$this->structureOrArray = $structureOrArray;
		$this->anotherStructureOrArray = $anotherStructureOrArray;
		$this->manyStructures = $manyStructures;
	}

}
