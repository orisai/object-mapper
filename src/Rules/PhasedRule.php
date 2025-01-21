<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;

/**
 * @template T_ARGS of Args
 * @extends Rule<T_ARGS>
 */
interface PhasedRule extends Rule
{

	/**
	 * @param mixed $value
	 * @param T_ARGS $args
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 */
	public function processValuePhase1(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	);

	/**
	 * @param array<int|string, mixed> $values
	 * @param T_ARGS $args
	 */
	public function processValuePhase2(
		array $values,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	): void;

	/**
	 * @param mixed $value
	 * @param T_ARGS $args
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 */
	public function processValuePhase3(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	);

}
