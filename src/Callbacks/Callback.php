<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Callbacks\Context\CallbackBaseContext;
use Orisai\ObjectMapper\Callbacks\Context\FieldContext;
use Orisai\ObjectMapper\Callbacks\Context\ObjectContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Context\MetaContext;
use Orisai\ObjectMapper\Processing\ObjectHolder;
use ReflectionClass;
use ReflectionProperty;
use Reflector;

/**
 * @template-covariant T_ARGS of Args
 */
interface Callback
{

	/**
	 * @param array<int|string, mixed> $args
	 * @param ReflectionClass<MappedObject>|ReflectionProperty $reflector
	 * @return T_ARGS
	 */
	public static function resolveArgs(array $args, MetaContext $context, Reflector $reflector): Args;

	/**
	 * @return class-string<T_ARGS>
	 */
	public static function getArgsType(): string;

	/**
	 * @param mixed $data
	 * @param T_ARGS $args
	 * @param ObjectContext|FieldContext $context
	 * @param ObjectHolder<MappedObject> $holder
	 * @param ReflectionClass<MappedObject> $declaringClass
	 * @return mixed
	 * @throws ValueDoesNotMatch
	 * @throws InvalidData
	 */
	public static function invoke(
		$data,
		Args $args,
		ObjectHolder $holder,
		CallbackBaseContext $context,
		ReflectionClass $declaringClass
	);

}
