<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\Context\BaseFieldContext;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\FieldSetContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\Processing\ObjectHolder;
use Orisai\ObjectMapper\ValueObject;

/**
 * @phpstan-template T_ARGS of Args
 */
interface Callback
{

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public static function resolveArgs(array $args, ArgsContext $context): array;

	/**
	 * @phpstan-return class-string<T_ARGS>
	 */
	public static function getArgsType(): string;

	/**
	 * @param mixed $data
	 * @param FieldContext|FieldSetContext $context
	 * @param ObjectHolder<ValueObject> $holder
	 * @phpstan-param T_ARGS $args
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 */
	public static function invoke($data, Args $args, ObjectHolder $holder, BaseFieldContext $context);

}
