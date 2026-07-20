<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Nette\Utils\Helpers;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Callbacks\Context\CallbackBaseContext;
use Orisai\ObjectMapper\Callbacks\Context\ObjectContext;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Context\MetaContext;
use Orisai\ObjectMapper\Meta\Runtime\PhpMethodMeta;
use Orisai\ObjectMapper\Processing\ObjectHolder;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use Reflector;
use function array_map;
use function assert;
use function in_array;
use function is_a;
use function sprintf;

/**
 * @implements Callback<AfterMappingCallbackArgs>
 *
 * @internal
 */
final class AfterMappingCallback implements Callback
{

	public const Method = 'method';

	private function __construct()
	{
		// Static constructor is required
	}

	public static function resolveArgs(
		array $args,
		MetaContext $context,
		Reflector $reflector
	): AfterMappingCallbackArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::Method]);

		$checker->checkRequiredArg(self::Method);
		$methodName = $checker->checkString(self::Method);

		if (!$reflector instanceof ReflectionClass) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'"%s" can be defined only above a class.',
					self::class,
				));
		}

		$method = self::validateMethod($reflector, $methodName);

		if ($method->isStatic()) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'"%s" must be used with a non-static method.',
					self::class,
				));
		}

		return new AfterMappingCallbackArgs(
			PhpMethodMeta::from($method),
		);
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	private static function validateMethod(ReflectionClass $class, string $methodName): ReflectionMethod
	{
		$method = self::validateMethodExistence($class, $methodName);

		self::validateMethodSignature($method);

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
					self::class,
					$class->getName(),
					$methodName,
					$hint !== null ? sprintf(' Did you mean "%s"?', $hint) : '',
				));
		}

		return $class->getMethod($methodName);
	}

	private static function validateMethodSignature(ReflectionMethod $method): void
	{
		[$paramContext] = self::validateParametersCount($method);

		self::validateClassMethodSignature($method, $paramContext);
	}

	/**
	 * @return array{ReflectionParameter|null}
	 */
	private static function validateParametersCount(ReflectionMethod $method): array
	{
		$requiredCount = $method->getNumberOfRequiredParameters();
		if ($requiredCount > 1) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Callback method %s::%s should have only 1 required parameter, %s required parameters given',
					$method->getDeclaringClass()->getName(),
					$method->getName(),
					$requiredCount,
				));
		}

		$parameters = $method->getParameters();

		return [
			$parameters[0] ?? null,
		];
	}

	/**
	 * afterClass(MappedObjectContext $context): void|never
	 */
	private static function validateClassMethodSignature(
		ReflectionMethod $method,
		?ReflectionParameter $paramContext
	): void
	{
		if ($paramContext !== null) {
			self::validateClassMethodContextParam($method, $paramContext);
		}

		self::validateClassMethodReturn($method);
	}

	protected static function validateClassMethodReturn(ReflectionMethod $method): void
	{
		$type = self::getTypeName($method->getReturnType());

		if (in_array($type, ['void', 'never'], true)) {
			return;
		}

		throw InvalidArgument::create()
			->withMessage(sprintf(
				'Return type of class callback method %s::%s should be "void" or "never" instead of %s',
				$method->getDeclaringClass()->getName(),
				$method->getName(),
				$type ?? 'none',
			));
	}

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

	protected static function getTypeName(?ReflectionType $type): ?string
	{
		if (!$type instanceof ReflectionNamedType) {
			return null;
		}

		return $type->getName();
	}

	public static function getArgsType(): string
	{
		return AfterMappingCallbackArgs::class;
	}

	/**
	 * @param AfterMappingCallbackArgs $args
	 * @return mixed
	 */
	public static function invoke(
		$data,
		Args $args,
		ObjectHolder $holder,
		CallbackBaseContext $context
	)
	{
		$meta = $args->meta;
		$method = $meta->method;

		$instance = $holder->getInstance();

		if ($meta->isPublic) {
			$instance->$method($context);
		} else {
			// phpcs:disable SlevomatCodingStandard.Functions.StaticClosure.ClosureNotStatic
			$bound = (fn () => $instance->$method($context))
				->bindTo($instance, $meta->declaringClass);
			// phpcs:enable
			assert($bound !== null);
			$bound();
		}

		return [];
	}

}
