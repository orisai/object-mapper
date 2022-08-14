<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\PhpTypes\Node;
use Orisai\ObjectMapper\Types\Type;

/**
 * @phpstan-template T_ARGS of Args
 */
interface Rule
{

	/**
	 * @param array<int|string, mixed> $args
	 * @return T_ARGS
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): Args;

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

	/**
	 * @phpstan-param T_ARGS $args
	 */
	public function getExpectedInputType(Args $args, TypeContext $context): Node;

	/**
	 * @phpstan-param T_ARGS $args
	 */
	public function getReturnType(Args $args, TypeContext $context): Node;

}
