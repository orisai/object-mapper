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
use ReflectionProperty;
use ReflectionType;
use function array_map;
use function count;
use function in_array;
use function is_a;
use function sprintf;

/**
 * @phpstan-implements Callback<BaseCallbackArgs>
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
		$checker->checkString(self::Method);

		$class = $context->getClass();
		$property = $context->getProperty();
		$methodName = $args[self::Method];
		$method = self::validateMethod($class, $methodName);
		self::validateMethodSignature($method, $class, $property);

		// Should be method called statically?
		$isStatic = $method->isStatic();

		// Method is expected to return data unless void return type is defined
		$returnType = $method->getReturnType();
		$returnsValue = !(
			$returnType instanceof ReflectionNamedType
			&& in_array($returnType->getName(), ['void', 'never'], true)
		);

		$runtime = CallbackRuntime::Process;
		if ($checker->hasArg(self::Runtime)) {
			$runtime = $checker->checkEnum(self::Runtime, [
				CallbackRuntime::Always,
				CallbackRuntime::Process,
				CallbackRuntime::ProcessWithoutMapping,
			]);
		}

		return new BaseCallbackArgs($methodName, $isStatic, $returnsValue, $runtime);
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	private static function validateMethod(ReflectionClass $class, string $methodName): ReflectionMethod
	{
		if (!$class->hasMethod($methodName)) {
			$methods = array_map(
				static fn (ReflectionMethod $method): string => $method->getName(),
				$class->getMethods(ReflectionMethod::IS_PUBLIC),
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

		$method = $class->getMethod($methodName);

		if ($method->isPrivate() && !$class->isFinal()) {
			// If you are reading this and want full support of private methods:
			//		- method must be called in context of class which defines it
			//		- private methods with same name in parent and child class must be unambiguous
			//		- both static and non-static methods must work
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Argument "%s" given to "%s" is expected to be public or protected method of "%s", ' .
					'private method %s" given. To use private method, change class to final.',
					self::Method,
					static::class,
					$class->getName(),
					$methodName,
				));
		}

		return $method;
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
		$params = $method->getParameters();
		$paramsCount = count($params);

		if ($paramsCount > 2) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Callback method %s::%s should have only 2 parameters, %s given',
					$class->getName(),
					$method->getName(),
					$paramsCount,
				));
		}

		$paramData = $params[0] ?? null;
		$paramContext = $params[1] ?? null;
		$returnType = $method->getReturnType();

		if ($property === null) { // Class method
			// beforeClass(<nothing>|mixed $data, MappedObjectContext $context): <anything>
			// afterClass(array $data, MappedObjectContext $context): array|void|never

			if ($paramData !== null) {
				$type = self::getTypeName($paramData->getType());
				if (static::class === BeforeCallback::class && !in_array($type, ['mixed', null], true)) {
					throw InvalidArgument::create()
						->withMessage(sprintf(
							'First parameter of class callback method %s::%s should have "mixed" or none type instead of %s',
							$class->getName(),
							$method->getName(),
							$type,
						));
				}

				if (static::class === AfterCallback::class && $type !== 'array') {
					throw InvalidArgument::create()
						->withMessage(sprintf(
							'First parameter of class callback method %s::%s should have "array" type instead of %s',
							$class->getName(),
							$method->getName(),
							$type ?? 'none',
						));
				}
			}

			if ($paramContext !== null && (
					($type = self::getTypeName($paramContext->getType())) === null
					|| !is_a($type, MappedObjectContext::class, true)
				)
			) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Second parameter of class callback method %s::%s should have "%s" (or child class) type instead of %s',
						$class->getName(),
						$method->getName(),
						MappedObjectContext::class,
						$type ?? 'none',
					));
			}

			if (
				static::class === AfterCallback::class
				&& !in_array(($type = self::getTypeName($returnType)), ['array', 'void', 'never'], true)
			) {
				throw InvalidArgument::create()
					->withMessage(sprintf(
						'Return type of class callback method %s::%s should be "array", "void" or "never" instead of %s',
						$class->getName(),
						$method->getName(),
						$type ?? 'none',
					));
			}
		} else {
			// Property method
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
						$type,
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
	 * @param mixed                            $data
	 * @param BaseCallbackArgs                 $args
	 * @param FieldContext|MappedObjectContext $context
	 * @return mixed
	 */
	public static function invoke($data, Args $args, ObjectHolder $holder, BaseFieldContext $context)
	{
		// Callback is skipped for unsupported runtime
		$runtimes = $context->shouldMapDataToObjects() ? self::InitializationRuntimes : self::ProcessingRuntimes;
		if (!in_array($args->runtime, $runtimes, true)) {
			return $data;
		}

		$method = $args->method;

		if ($args->isStatic) {
			$class = $holder->getClass();
			$callbackOutput = (static fn () => $class::$method($data, $context))
				->bindTo(null, $class)();
		} else {
			$instance = $holder->getInstance();
			// Closure with bound instance cannot be static
			// phpcs:disable SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic
			$callbackOutput = (fn () => $instance->$method($data, $context))
				->bindTo($instance, $instance)();
		}

		return $args->returnsValue ? $callbackOutput : $data;
	}

}
