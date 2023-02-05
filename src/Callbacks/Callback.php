<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Context\BaseFieldContext;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\MappedObjectContext;
use Orisai\ObjectMapper\Context\ResolverArgsContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\ObjectHolder;
use ReflectionClass;

/**
 * @template T_ARGS of Args
 */
interface Callback
{

	/**
	 * @param array<int|string, mixed> $args
	 * @return T_ARGS
	 */
	public static function resolveArgs(array $args, ResolverArgsContext $context): Args;

	/**
	 * @return class-string<T_ARGS>
	 */
	public static function getArgsType(): string;

	/**
	 * @param mixed                            $data
	 * @param T_ARGS                   $args
	 * @param FieldContext|MappedObjectContext $context
	 * @param ObjectHolder<MappedObject>       $holder
	 * @param ReflectionClass<MappedObject>    $declaringClass
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 */
	public static function invoke(
		$data,
		Args $args,
		ObjectHolder $holder,
		BaseFieldContext $context,
		ReflectionClass $declaringClass
	);

}
