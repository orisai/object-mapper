<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;

/**
 * @template T_ARGS of Args
 * @extends Rule<T_ARGS>
 */
interface MultiValueEfficientRule extends Rule
{

	/**
	 * @param mixed  $value
	 * @param T_ARGS $args
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 */
	public function processValuePhase1($value, Args $args, FieldContext $context);

	/**
	 * @param list<mixed> $values
	 * @param T_ARGS      $args
	 */
	public function processValuePhase2(array $values, Args $args, FieldContext $context): void;

	/**
	 * @param mixed  $value
	 * @param T_ARGS $args
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 */
	public function processValuePhase3($value, Args $args, FieldContext $context);

}
