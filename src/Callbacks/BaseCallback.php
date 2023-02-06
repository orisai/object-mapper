<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Nette\Utils\Helpers;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\BaseFieldContext;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\MappedObjectContext;
use Orisai\ObjectMapper\Context\ResolverArgsContext;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\ObjectHolder;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use function array_map;
use function count;
use function in_array;
use function is_a;
use function sprintf;

/**
 * @implements Callback<BaseCallbackArgs>
 *
 * @internal
 */
abstract class BaseCallback implements Callback
{

	private const
		Method = 'method',
		Runtime = 'runtime';

	private const ProcessingRuntimes = [
		CallbackRuntime::Always,
		CallbackRuntime::ProcessWithoutMapping,
	];

	private const InitializationRuntimes = [
		CallbackRuntime::Always,
		CallbackRuntime::Process,
	];

	private function __construct()
	{
		// Static constructor is required
	}

	public static function resolveArgs(array $args, ResolverArgsContext $context): BaseCallbackArgs
	{
		$checker = new ArgsChecker($args, static::class);
		$checker->checkAllowedArgs([self::Method, self::Runtime]);

		$checker->checkRequiredArg(self::Method);
		$methodName = $checker->checkString(self::Method);

		$runtime = CallbackRuntime::Process;
		if ($checker->hasArg(self::Runtime)) {
			$runtime = $checker->checkEnum(self::Runtime, [
				CallbackRuntime::Always,
				CallbackRuntime::Process,
				CallbackRuntime::ProcessWithoutMapping,
			]);
		}

		$class = $context->getClass();
		$property = $context->getProperty();
		$method = self::validateMethod($class, $property, $methodName);

		return new BaseCallbackArgs(
			$methodName,
			$method->isStatic(),
			self::getMethodReturnsValue($method),
			CallbackRuntime::from($runtime),
		);
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	private static function validateMethod(
		ReflectionClass $class,
		?ReflectionProperty $property,
		string $methodName
	): ReflectionMethod
	{
		$method = self::validateMethodExistence($class, $methodName);

		self::validateMethodSignature($method, $class, $property);

		return $method;
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	private static function validateMethodExistence(ReflectionClass $class, string $methodName): ReflectionMethod
	{
		if (!$class->hasMethod($methodName)) {
			$methods = array_map(
				static fn (ReflectionMethod $method): string => $method->getName(),
				$class->getMethods(),
			);
			$hint = Helpers::getSuggestion($methods, $methodName);

			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Argument "%s" given to "%s" is expected to be existing method of "%s", "%s" given.%s',
					self::Method,
					static::class,
					$class->getName(),
					$methodName,
					$hint !== null ? sprintf(' Did you mean "%s"?', $hint) : '',
				));
		}

		return $class->getMethod($methodName);
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	private static function validateMethodSignature(
		ReflectionMethod $method,
		ReflectionClass $class,
		?ReflectionProperty $property
	): void
	{
		[$paramData, $paramContext] = self::validateParametersCount($class, $method);

		$property === null
			? self::validateClassMethodSignature($class, $method, $paramData, $paramContext)
			: self::validatePropertyMethodSignature($class, $method, $paramData, $paramContext);
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 * @return array{ReflectionParameter|null, ReflectionParameter|null}
	 */
	private static function validateParametersCount(ReflectionClass $class, ReflectionMethod $method): array
	{
		$parameters = $method->getParameters();

		$count = count($parameters);
		if ($count > 2) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Callback method %s::%s should have only 2 parameters, %s given',
					$class->getName(),
					$method->getName(),
					$count,
				));
		}

		return [
			$parameters[0] ?? null,
			$parameters[1] ?? null,
		];
	}

	/**
	 * beforeClass(<nothing>|mixed $data, MappedObjectContext $context): <anything>
	 * afterClass(array $data, MappedObjectContext $context): array|void|never
	 *
	 * @param ReflectionClass<MappedObject> $class
	 */
	private static function validateClassMethodSignature(
		ReflectionClass $class,
		ReflectionMethod $method,
		?ReflectionParameter $paramData,
		?ReflectionParameter $paramContext
	): void
	{
		if ($paramData !== null) {
			static::validateClassMethodDataParam($class, $method, $paramData);
		}

		if ($paramContext !== null) {
			self::validateClassMethodContextParam($class, $method, $paramContext);
		}

		static::validateClassMethodReturn($class, $method);
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	abstract protected static function validateClassMethodDataParam(
		ReflectionClass $class,
		ReflectionMethod $method,
		ReflectionParameter $paramData
	): void;

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	abstract protected static function validateClassMethodReturn(
		ReflectionClass $class,
		ReflectionMethod $method
	): void;

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	private static function validateClassMethodContextParam(
		ReflectionClass $class,
		ReflectionMethod $method,
		ReflectionParameter $paramContext
	): void
	{
		if (
			($type = self::getTypeName($paramContext->getType())) === null
			|| !is_a($type, MappedObjectContext::class, true)
		) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Second parameter of class callback method %s::%s should have "%s" type instead of %s',
					$class->getName(),
					$method->getName(),
					MappedObjectContext::class,
					$type ?? 'none',
				));
		}
	}

	/**
	 * beforeField(<nothing>|mixed $data, FieldContext $context): <anything>
	 * afterField(<anything> $data, FieldContext $context): <anything>
	 *
	 * @param ReflectionClass<MappedObject> $class
	 */
	private static function validatePropertyMethodSignature(
		ReflectionClass $class,
		ReflectionMethod $method,
		?ReflectionParameter $paramData,
		?ReflectionParameter $paramContext
	): void
	{
		if ($paramData !== null) {
			static::validatePropertyMethodDataParam($class, $method, $paramData);
		}

		if ($paramContext !== null) {
			self::validatePropertyMethodContextParam($class, $method, $paramContext);
		}
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	abstract protected static function validatePropertyMethodDataParam(
		ReflectionClass $class,
		ReflectionMethod $method,
		ReflectionParameter $paramData
	): void;

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	private static function validatePropertyMethodContextParam(
		ReflectionClass $class,
		ReflectionMethod $method,
		ReflectionParameter $paramContext
	): void
	{
		$type = self::getTypeName($paramContext->getType());

		if ($type !== null && is_a($type, FieldContext::class, true)) {
			return;
		}

		throw InvalidArgument::create()
			->withMessage(sprintf(
				'Second parameter of field callback method %s::%s should have "%s" type instead of %s',
				$class->getName(),
				$method->getName(),
				FieldContext::class,
				$type ?? 'none',
			));
	}

	protected static function getTypeName(?ReflectionType $type): ?string
	{
		if (!$type instanceof ReflectionNamedType) {
			return null;
		}

		return $type->getName();
	}

	/**
	 * Method is expected to return data unless void or never return type is defined
	 */
	private static function getMethodReturnsValue(ReflectionMethod $method): bool
	{
		return !in_array(self::getTypeName($method->getReturnType()), ['void', 'never'], true);
	}

	public static function getArgsType(): string
	{
		return BaseCallbackArgs::class;
	}

	/**
	 * @param mixed                            $data
	 * @param BaseCallbackArgs                 $args
	 * @param FieldContext|MappedObjectContext $context
	 * @return mixed
	 */
	public static function invoke(
		$data,
		Args $args,
		ObjectHolder $holder,
		BaseFieldContext $context,
		ReflectionClass $declaringClass
	)
	{
		// Callback is skipped for unsupported runtime
		$runtimes = $context->shouldMapDataToObjects() ? self::InitializationRuntimes : self::ProcessingRuntimes;
		if (!in_array($args->runtime->value, $runtimes, true)) {
			return $data;
		}

		$method = $args->method;

		if ($args->isStatic) {
			$class = $holder->getClass();
			$callbackOutput = (static fn () => $class::$method($data, $context))
				->bindTo(null, $declaringClass->getName())();
		} else {
			$instance = $holder->getInstance();
			// Closure with bound instance cannot be static
			// phpcs:disable SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic
			$callbackOutput = (fn () => $instance->$method($data, $context))
				->bindTo($instance, $declaringClass->getName())();
		}

		return $args->returnsValue ? $callbackOutput : $data;
	}

}
