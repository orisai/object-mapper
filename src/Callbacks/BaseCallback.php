<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Nette\Utils\ObjectHelpers;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Context\ArgsContext;
use Orisai\ObjectMapper\Context\BaseFieldContext;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\FieldSetContext;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\Meta\ArgsChecker;
use Orisai\ObjectMapper\Processing\ObjectHolder;
use Orisai\ObjectMapper\ValueObject;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use function array_map;
use function count;
use function in_array;
use function is_a;
use function sprintf;

/**
 * @implements Callback<BaseCallbackArgs>
 */
abstract class BaseCallback implements Callback
{

	// User defined
	public const METHOD = 'method';
	public const RUNTIME = 'runtime';

	// Internal
	public const METHOD_IS_STATIC = 'method_is_static';
	public const METHOD_RETURNS_VALUE = 'method_returns_value';

	private const PROCESSING_RUNTIMES = [
		CallbackRuntime::ALWAYS,
		CallbackRuntime::PROCESSING,
	];

	private const INITIALIZATION_RUNTIMES = [
		CallbackRuntime::ALWAYS,
		CallbackRuntime::INITIALIZATION,
	];

	private function __construct()
	{
	}

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public static function resolveArgs(array $args, ArgsContext $context): array
	{
		$checker = new ArgsChecker($args, static::class);
		$checker->checkAllowedArgs([self::METHOD, self::RUNTIME]);

		$checker->checkRequiredArg(self::METHOD);
		$checker->checkString(self::METHOD);

		if ($checker->hasArg(self::RUNTIME)) {
			$checker->checkEnum(self::RUNTIME, CallbackRuntime::ALL);
		}

		$class = $context->getClass();
		$property = $context->getProperty();
		$methodName = $args[self::METHOD];
		$method = self::validateMethod($class, $methodName);
		self::validateMethodSignature($method, $class, $property);

		// Should be method called statically?
		$args[self::METHOD_IS_STATIC] = $method->isStatic();

		// Method is expected to return data unless void return type is defined
		$returnType = $method->getReturnType();
		$args[self::METHOD_RETURNS_VALUE] = !($returnType instanceof ReflectionNamedType && $returnType->getName() === 'void');

		return $args;
	}

	/**
	 * @phpstan-param ReflectionClass<ValueObject> $class
	 */
	private static function validateMethod(ReflectionClass $class, string $methodName): ReflectionMethod
	{
		if (!$class->hasMethod($methodName)) {
			$methods = array_map(static fn (ReflectionMethod $method): string => $method->getName(), $class->getMethods(ReflectionMethod::IS_PUBLIC));
			$hint = ObjectHelpers::getSuggestion($methods, $methodName);

			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Argument "%s" given to "%s" is expected to be existing method of "%s", "%s" given.%s',
					self::METHOD,
					static::class,
					$class->getName(),
					$methodName,
					$hint !== null ? sprintf(' Did you mean "%s"?', $hint) : '',
				));
		}

		$method = $class->getMethod($methodName);

		if (!$method->isPublic()) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Argument "%s" given to "%s" is expected to be public method of "%s", "%s method %s" given.',
					self::METHOD,
					static::class,
					$class->getName(),
					$method->isProtected() ? 'protected' : 'private',
					$methodName,
				));
		}

		return $method;
	}

	/**
	 * @phpstan-param ReflectionClass<ValueObject> $class
	 */
	private static function validateMethodSignature(
		ReflectionMethod $method,
		ReflectionClass $class,
		?ReflectionProperty $property
	): void
	{
		$params = $method->getParameters();
		$paramsCount = count($params);

		if ($paramsCount > 2) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Callback method %ss::%s should have only 2 parameters, %s given',
					$class->getName(),
					$method->getName(),
					$paramsCount,
				));
		}

		$paramData = $params[0] ?? null;
		$paramContext = $params[1] ?? null;
		$returnType = $method->getReturnType();

		if ($property === null) { // Class method
			// beforeClass(array $data, FieldSetContext $context): array|void
			// afterClass(array $data, FieldSetContext $context): array|void

			if ($paramData !== null && ($type = self::getTypeName($paramData->getType())) !== 'array') {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'First parameter of class callback method %s::%s should have "array" type instead of %s',
						$class->getName(),
						$method->getName(),
						$type ?? 'none',
					));
			}

			if ($paramContext !== null && (
					($type = self::getTypeName($paramContext->getType())) === null
					|| !is_a($type, FieldSetContext::class, true)
				)
			) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Second parameter of class callback method %s::%s should have "%s" (or child class) type instead of %s',
						$class->getName(),
						$method->getName(),
						FieldSetContext::class,
						$type ?? 'none',
					));
			}

			if (!in_array(($type = self::getTypeName($returnType)), ['array', 'void'], true)) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Return type of class callback method %s::%s should be "array" or "void" instead of %s',
						$class->getName(),
						$method->getName(),
						$type ?? 'none',
					));
			}
		} else { // Property method
			// beforeField(<nothing>|mixed $data, FieldContext $context): <anything>
			// afterField(<anything> $data, FieldContext $context): <anything>

			if (
				static::class === BeforeCallback::class
				&& $paramData !== null
				&& !in_array(($type = self::getTypeName($paramData->getType())), [null, 'mixed'], true)
			) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'First parameter of before field callback method %s::%s should have none or "mixed" type instead of %s',
						$class->getName(),
						$method->getName(),
						$type ?? 'none',
					));
			}

			if ($paramContext !== null && (
					($type = self::getTypeName($paramContext->getType())) === null
					|| !is_a($type, FieldContext::class, true)
				)
			) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Second parameter of field callback method %s::%s should have "%s" (or child class) type instead of %s',
						$class->getName(),
						$method->getName(),
						FieldContext::class,
						$type ?? 'none',
					));
			}
		}
	}

	private static function getTypeName(?ReflectionType $type): ?string
	{
		if (!$type instanceof ReflectionNamedType) {
			return null;
		}

		return $type->getName();
	}

	public static function getArgsType(): string
	{
		return BaseCallbackArgs::class;
	}

	/**
	 * @param mixed $data
	 * @param BaseCallbackArgs $args
	 * @param FieldContext|FieldSetContext $context
	 * @return mixed
	 */
	public static function invoke($data, Args $args, ObjectHolder $holder, BaseFieldContext $context)
	{
		// Callback is skipped for unsupported runtime
		if (!in_array($args->runtime, $context->isInitializeObjects() ? self::INITIALIZATION_RUNTIMES : self::PROCESSING_RUNTIMES, true)) {
			return $data;
		}

		$method = $args->method;

		$callbackOutput = $args->isStatic
			? $holder->getClass()::$method($data, $context)
			: $holder->getInstance()->$method($data, $context);

		return $args->returnsValue ? $callbackOutput : $data;
	}

}
