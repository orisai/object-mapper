<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use BackedEnum;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\PhpTypes\CompoundNode;
use Orisai\ObjectMapper\PhpTypes\LiteralNode;
use Orisai\ObjectMapper\PhpTypes\Node;
use Orisai\ObjectMapper\PhpTypes\SimpleNode;
use Orisai\ObjectMapper\Types\EnumType;
use Orisai\ObjectMapper\Types\Value;
use TypeError;
use function is_string;
use function is_subclass_of;
use const PHP_VERSION_ID;

/**
 * @phpstan-implements Rule<BackedEnumArgs>
 */
final class BackedEnumRule implements Rule
{

	private const
		ClassName = 'class',
		AllowUnknown = 'allowUnknown';

	public function __construct()
	{
		if (PHP_VERSION_ID < 8_01_00) {
			throw InvalidState::create()
				->withMessage(self::class . ' can be used only with PHP 8.1+');
		}
	}

	public function resolveArgs(array $args, RuleArgsContext $context): BackedEnumArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::ClassName, self::AllowUnknown]);

		$checker->checkRequiredArg(self::ClassName);
		$class = $args[self::ClassName];

		if (!is_string($class) || !is_subclass_of($class, BackedEnum::class)) {
			throw InvalidArgument::create()
				->withMessage($checker->formatMessage(
					BackedEnum::class,
					self::ClassName,
					$class,
				));
		}

		$allowUnknown = false;
		if ($checker->hasArg(self::AllowUnknown)) {
			$allowUnknown = $checker->checkBool(self::AllowUnknown);
		}

		return new BackedEnumArgs($class, $allowUnknown);
	}

	public function getArgsType(): string
	{
		return BackedEnumArgs::class;
	}

	/**
	 * @param mixed          $value
	 * @param BackedEnumArgs $args
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context): ?BackedEnum
	{
		$class = $args->class;

		try {
			$enum = $class::tryFrom($value);
		} catch (TypeError $error) {
			throw ValueDoesNotMatch::create($this->createType($args, $context), Value::of($value));
		}

		if ($enum !== null) {
			return $enum;
		}

		if ($args->allowUnknown) {
			return null;
		}

		throw ValueDoesNotMatch::create($this->createType($args, $context), Value::of($value));
	}

	/**
	 * @param BackedEnumArgs $args
	 */
	public function createType(Args $args, TypeContext $context): EnumType
	{
		return new EnumType($this->getEnumValues($args));
	}

	/**
	 * @return array<int, int|string>
	 */
	private function getEnumValues(BackedEnumArgs $args): array
	{
		$class = $args->class;

		$values = [];
		foreach ($class::cases() as $case) {
			$values[] = $case->value;
		}

		return $values;
	}

	/**
	 * @param BackedEnumArgs $args
	 */
	public function getExpectedInputType(Args $args, TypeContext $context): Node
	{
		return CompoundNode::createOrType($this->createNodes($args));
	}

	/**
	 * @param BackedEnumArgs $args
	 */
	public function getReturnType(Args $args, TypeContext $context): Node
	{
		$return = new SimpleNode($args->class);

		if ($args->allowUnknown) {
			return CompoundNode::createOrType([
				$return,
				new SimpleNode('null'),
			]);
		}

		return $return;
	}

	/**
	 * @return array<int, Node>
	 */
	private function createNodes(BackedEnumArgs $args): array
	{
		$class = $args->class;

		$nodes = [];
		foreach ($class::cases() as $case) {
			$nodes[] = new LiteralNode($case->value);
		}

		return $nodes;
	}

}
