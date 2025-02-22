<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Nette\Utils\Helpers;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Callbacks\Context\CallbackBaseContext;
use Orisai\ObjectMapper\Callbacks\Context\FieldContext;
use Orisai\ObjectMapper\Callbacks\Context\ObjectContext;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Context\MetaContext;
use Orisai\ObjectMapper\Meta\Runtime\PhpMethodMeta;
use Orisai\ObjectMapper\Processing\ObjectHolder;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use Reflector;
use function array_map;
use function in_array;
use function is_a;
use function sprintf;

/**
 * @implements Callback<ValidationCallbackArgs>
 *
 * @internal
 */
abstract class ValidationCallback implements Callback
{

	public const
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

	public static function resolveArgs(
		array $args,
		MetaContext $context,
		Reflector $reflector
	): ValidationCallbackArgs
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

		if ($reflector instanceof ReflectionProperty) {
			/** @var ReflectionClass<MappedObject> $class */
			$class = $reflector->getDeclaringClass();
			$property = $reflector;
		} else {
			$class = $reflector;
			$property = null;
		}

		$method = self::validateMethod($class, $property, $methodName);

		return new ValidationCallbackArgs(
			CallbackRuntime::from($runtime),
			PhpMethodMeta::from($method),
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

		self::validateMethodSignature($method, $property);

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

	private static function validateMethodSignature(
		ReflectionMethod $method,
		?ReflectionProperty $property
	): void
	{
		[$paramData, $paramContext] = self::validateParametersCount($method);

		$property === null
			? self::validateClassMethodSignature($method, $paramData, $paramContext)
			: self::validatePropertyMethodSignature($method, $paramData, $paramContext);
	}

	/**
	 * @return array{ReflectionParameter|null, ReflectionParameter|null}
	 */
	private static function validateParametersCount(ReflectionMethod $method): array
	{
		$requiredCount = $method->getNumberOfRequiredParameters();
		if ($requiredCount > 2) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Callback method %s::%s should have only 2 required parameters, %s required parameters given',
					$method->getDeclaringClass()->getName(),
					$method->getName(),
					$requiredCount,
				));
		}

		$parameters = $method->getParameters();

		return [
			$parameters[0] ?? null,
			$parameters[1] ?? null,
		];
	}

	/**
	 * beforeClass(<nothing>|mixed $data, MappedObjectContext $context): <anything>
	 * afterClass(array $data, MappedObjectContext $context): array|void|never
	 */
	private static function validateClassMethodSignature(
		ReflectionMethod $method,
		?ReflectionParameter $paramData,
		?ReflectionParameter $paramContext
	): void
	{
		if ($paramData !== null) {
			static::validateClassMethodDataParam($method, $paramData);
		}

		if ($paramContext !== null) {
			self::validateClassMethodContextParam($method, $paramContext);
		}

		static::validateClassMethodReturn($method);
	}

	abstract protected static function validateClassMethodDataParam(
		ReflectionMethod $method,
		ReflectionParameter $paramData
	): void;

	abstract protected static function validateClassMethodReturn(
		ReflectionMethod $method
	): void;

	private static function validateClassMethodContextParam(
		ReflectionMethod $method,
		ReflectionParameter $paramContext
	): void
	{
		if (
			($type = self::getTypeName($paramContext->getType())) === null
			|| !is_a($type, ObjectContext::class, true)
		) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Second parameter of class callback method %s::%s should have "%s" type instead of %s',
					$method->getDeclaringClass()->getName(),
					$method->getName(),
					ObjectContext::class,
					$type ?? 'none',
				));
		}
	}

	/**
	 * beforeField(<nothing>|mixed $data, FieldContext $context): <anything>
	 * afterField(<anything> $data, FieldContext $context): <anything>
	 */
	private static function validatePropertyMethodSignature(
		ReflectionMethod $method,
		?ReflectionParameter $paramData,
		?ReflectionParameter $paramContext
	): void
	{
		if ($paramData !== null) {
			static::validatePropertyMethodDataParam($method, $paramData);
		}

		if ($paramContext !== null) {
			self::validatePropertyMethodContextParam($method, $paramContext);
		}
	}

	abstract protected static function validatePropertyMethodDataParam(
		ReflectionMethod $method,
		ReflectionParameter $paramData
	): void;

	private static function validatePropertyMethodContextParam(
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
				$method->getDeclaringClass()->getName(),
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

	public static function getArgsType(): string
	{
		return ValidationCallbackArgs::class;
	}

	/**
	 * @param ValidationCallbackArgs $args
	 * @return mixed
	 */
	public static function invoke(
		$data,
		Args $args,
		ObjectHolder $holder,
		CallbackBaseContext $context
	)
	{
		// Callback is skipped for unsupported runtime
		$runtimes = $context->shouldInitializeObjects() ? self::InitializationRuntimes : self::ProcessingRuntimes;
		if (!in_array($args->runtime->value, $runtimes, true)) {
			return $data;
		}

		$meta = $args->meta;
		$method = $meta->method;

		if ($meta->isStatic) {
			$class = $holder->getClass();

			$callbackOutput = $meta->isPublic
				? $class::$method($data, $context)
				: (static fn () => $class::$method($data, $context))
				->bindTo(null, $meta->declaringClass)();
		} else {
			$instance = $holder->getInstance();

			// phpcs:disable SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic
			$callbackOutput = $meta->isPublic
				? $instance->$method($data, $context)
				: (fn () => $instance->$method($data, $context))
				->bindTo($instance, $meta->declaringClass)();
			// phpcs:enable
		}

		return $meta->returnsValue ? $callbackOutput : $data;
	}

}
