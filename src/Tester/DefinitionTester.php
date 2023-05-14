<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Tester;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Callbacks\CallbackDefinition;
use Orisai\ObjectMapper\Modifiers\ModifierDefinition;
use Orisai\ObjectMapper\Rules\RuleDefinition;
use ReflectionClass;
use function array_keys;
use function array_map;
use function implode;
use function in_array;
use function interface_exists;
use function is_a;
use function str_contains;
use const PHP_VERSION_ID;

final class DefinitionTester
{

	private const AttributeFlags = [
		Attribute::TARGET_CLASS => 'TARGET_CLASS',
		Attribute::TARGET_FUNCTION => 'TARGET_FUNCTION',
		Attribute::TARGET_METHOD => 'TARGET_METHOD',
		Attribute::TARGET_PROPERTY => 'TARGET_PROPERTY',
		Attribute::TARGET_CLASS_CONSTANT => 'TARGET_CLASS_CONSTANT',
		Attribute::TARGET_PARAMETER => 'TARGET_PARAMETER',
		Attribute::TARGET_ALL => 'TARGET_ALL',
		Attribute::IS_REPEATABLE => 'IS_REPEATABLE',
	];

	private const AnnotationTargetFlags = [
		Target::TARGET_CLASS => 'CLASS',
		Target::TARGET_FUNCTION => 'FUNCTION',
		Target::TARGET_METHOD => 'METHOD',
		Target::TARGET_PROPERTY => 'PROPERTY',
		Target::TARGET_ALL => 'ALL',
		Target::TARGET_ANNOTATION => 'ANNOTATION',
	];

	/**
	 * @param class-string $class
	 */
	public static function assertIsRuleAttribute(string $class): void
	{
		self::assertClassType($class, RuleDefinition::class);
		self::assertIsAttribute($class, [Attribute::TARGET_PROPERTY]);
	}

	/**
	 * @param class-string $class
	 */
	public static function assertIsRuleAnnotation(string $class): void
	{
		self::assertClassType($class, RuleDefinition::class);
		self::assertIsAnnotation($class, [Target::TARGET_ANNOTATION, Target::TARGET_PROPERTY]);
	}

	/**
	 * @param class-string $class
	 */
	public static function assertIsCallbackAttribute(string $class): void
	{
		self::assertClassType($class, CallbackDefinition::class);
		self::assertIsAttribute($class, [Attribute::TARGET_CLASS, Attribute::TARGET_PROPERTY]);
	}

	/**
	 * @param class-string $class
	 */
	public static function assertIsCallbackAnnotation(string $class): void
	{
		self::assertClassType($class, CallbackDefinition::class);
		self::assertIsAnnotation($class, [Target::TARGET_CLASS, Target::TARGET_PROPERTY]);
	}

	/**
	 * @param class-string              $class
	 * @param list<Attribute::TARGET_*> $targets
	 */
	public static function assertIsModifierAttribute(string $class, array $targets): void
	{
		self::assertClassType($class, ModifierDefinition::class);
		self::assertIsAttribute($class, $targets);
	}

	/**
	 * @param class-string           $class
	 * @param list<Target::TARGET_*> $targets
	 */
	public static function assertIsModifierAnnotation(string $class, array $targets): void
	{
		self::assertClassType($class, ModifierDefinition::class);
		self::assertIsAnnotation($class, $targets);
	}

	/**
	 * @param class-string $class
	 * @param class-string $type
	 */
	private static function assertClassType(string $class, string $type): void
	{
		if (!is_a($class, $type, true)) {
			throw InvalidArgument::create()
				->withMessage("'$class' does not implement '$type'.");
		}
	}

	/**
	 * @param class-string                       $class
	 * @param list<key-of<self::AttributeFlags>> $requiredFlags
	 */
	private static function assertIsAttribute(string $class, array $requiredFlags): void
	{
		if (PHP_VERSION_ID < 8_00_00) {
			throw InvalidState::create()
				->withMessage('Attributes are testable only with PHP >= 8.0');
		}

		$reflector = new ReflectionClass($class);

		$attributeClass = Attribute::class;
		$attribute = $reflector->getAttributes($attributeClass)[0] ?? null;
		if ($attribute === null) {
			throw InvalidArgument::create()
				->withMessage("'$class' does not define attribute '#[$attributeClass]'.");
		}

		$args = $attribute->getArguments();
		$givenFlags = $args[0] ?? $args['flags'] ?? null;

		$givenFlags = self::bitwiseValueToArray(
			$givenFlags,
			array_keys(self::AttributeFlags),
		);

		foreach ($requiredFlags as $requiredFlag) {
			if (!in_array($requiredFlag, $givenFlags, true)) {
				$targetName = self::AttributeFlags[$requiredFlag];

				throw InvalidArgument::create()
					->withMessage("Attribute '$attributeClass' of class '$class' must define target '$targetName'.");
			}
		}

		foreach ($givenFlags as $givenFlag) {
			if (!in_array($givenFlag, $requiredFlags, true)) {
				$givenFlagName = self::AttributeFlags[$givenFlag];

				$allowedFlagNames = implode(
					"', '",
					array_map(
						static fn (int $value) => "$attributeClass::" . self::AttributeFlags[$value],
						$requiredFlags,
					),
				);

				// If you need flag to be just contained and not being matched exactly, please open an issue
				throw InvalidArgument::create()
					->withMessage("Attribute '$attributeClass' of class '$class' must define only allowed flags,"
						. " flag '$attributeClass::$givenFlagName' given. Allowed are: '$allowedFlagNames'.");
			}
		}
	}

	/**
	 * @param class-string                              $class
	 * @param list<key-of<self::AnnotationTargetFlags>> $requiredTargets
	 */
	private static function assertIsAnnotation(string $class, array $requiredTargets): void
	{
		if (!interface_exists(Reader::class)) {
			throw InvalidState::create()
				->withMessage('Annotations are testable only with doctrine/annotations installed.');
		}

		$reflector = new ReflectionClass($class);
		$reader = new AnnotationReader();

		$comment = $reflector->getDocComment();
		if ($comment === false || !str_contains($comment, '@Annotation')) {
			throw InvalidArgument::create()
				->withMessage("'$class' does not define annotation '@Annotation'.");
		}

		$namedArgCtorClass = NamedArgumentConstructor::class;
		if ($reader->getClassAnnotation($reflector, $namedArgCtorClass) === null) {
			throw InvalidArgument::create()
				->withMessage("'$class' does not define annotation '@$namedArgCtorClass'."
					. ' It is required for attributes compatibility.');
		}

		$targetClass = Target::class;
		$targetAnnotation = $reader->getClassAnnotation($reflector, $targetClass);
		if ($targetAnnotation === null) {
			throw InvalidArgument::create()
				->withMessage("'$class' does not define annotation '$targetClass'.");
		}

		$givenTargets = self::bitwiseValueToArray(
			$targetAnnotation->targets,
			array_keys(self::AnnotationTargetFlags),
		);

		foreach ($requiredTargets as $requiredTarget) {
			if (!in_array($requiredTarget, $givenTargets, true)) {
				$targetName = self::AnnotationTargetFlags[$requiredTarget];

				throw InvalidArgument::create()
					->withMessage("Annotation '$targetClass' of class '$class' must define target '$targetName'.");
			}
		}

		foreach ($givenTargets as $givenTarget) {
			if (!in_array($givenTarget, $requiredTargets, true)) {
				$givenTargetName = self::AnnotationTargetFlags[$givenTarget];

				$allowedTargetNames = implode(
					"', '",
					array_map(
						static fn (int $value) => self::AnnotationTargetFlags[$value],
						$requiredTargets,
					),
				);

				// If you need flag to be just contained and not being matched exactly, please open an issue
				throw InvalidArgument::create()
					->withMessage("Annotation '$targetClass' of class '$class' must define only allowed targets,"
						. " target '$givenTargetName' given. Allowed are: '$allowedTargetNames'.");
			}
		}
	}

	/**
	 * @template T of int
	 * @param list<T> $possibleFlags
	 * @return list<T>
	 */
	private static function bitwiseValueToArray(int $bitwiseValue, array $possibleFlags): array
	{
		$flags = [];
		foreach ($possibleFlags as $flag) {
			if (($bitwiseValue & $flag) === $flag) {
				$flags[] = $flag;
			}
		}

		return $flags;
	}

}
