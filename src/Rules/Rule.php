<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\Types\Type;

/**
 * @phpstan-template T_ARGS of Args
 */
interface Rule
{

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): array;

	/**
	 * @phpstan-return class-string<T_ARGS>
	 */
	public function getArgsType(): string;

	/**
	 * @param mixed $value
	 * @phpstan-param T_ARGS $args
	 * @return mixed
	 * @throws ValueDoesNotMatch Value does not match rule or rule args
	 * @throws InvalidData Error bubbled from inner processor call
	 */
	public function processValue($value, Args $args, FieldContext $context);

	/**
	 * @phpstan-param T_ARGS $args
	 */
	public function createType(Args $args, TypeContext $context): Type;

}
