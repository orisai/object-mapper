<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Context\MetaFieldContext;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Types\Type;

/**
 * @template-covariant T_ARGS of Args
 */
interface Rule
{

	/**
	 * @param array<int|string, mixed> $args
	 * @return T_ARGS
	 */
	public function resolveArgs(array $args, MetaFieldContext $context): Args;

	/**
	 * @return class-string<T_ARGS>
	 */
	public function getArgsType(): string;

	/**
	 * @param mixed $value
	 * @param T_ARGS $args
	 * @return mixed
	 * @throws ValueDoesNotMatch Value does not match rule or rule args
	 * @throws InvalidData Error bubbled from inner processor call
	 */
	public function processValue(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	);

	/**
	 * @param T_ARGS $args
	 */
	public function createType(
		Args $args,
		ServicesContext $services,
		DynamicContext $dynamic
	): Type;

}
