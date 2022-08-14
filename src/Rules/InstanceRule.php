<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\PhpTypes\Node;
use Orisai\ObjectMapper\PhpTypes\SimpleNode;
use Orisai\ObjectMapper\Types\SimpleValueType;
use Orisai\ObjectMapper\Types\Value;
use function class_exists;
use function interface_exists;
use function is_string;

/**
 * @phpstan-implements Rule<InstanceArgs>
 */
final class InstanceRule implements Rule
{

	private const Type = 'type';

	public function resolveArgs(array $args, RuleArgsContext $context): InstanceArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::Type]);

		$checker->checkRequiredArg(self::Type);
		$type = $args[self::Type];

		if (!is_string($type) || (!class_exists($type) && !interface_exists($type))) {
			throw InvalidArgument::create()
				->withMessage($checker->formatMessage(
					'class|interface',
					self::Type,
					$type,
				));
		}

		return new InstanceArgs($type);
	}

	public function getArgsType(): string
	{
		return InstanceArgs::class;
	}

	/**
	 * @param mixed $value
	 * @param InstanceArgs $args
	 * @throws ValueDoesNotMatch
	 */
	public function processValue($value, Args $args, FieldContext $context): object
	{
		if ($value instanceof $args->type) {
			return $value;
		}

		throw ValueDoesNotMatch::create($this->createType($args, $context), Value::of($value));
	}

	/**
	 * @param InstanceArgs $args
	 */
	public function createType(Args $args, TypeContext $context): SimpleValueType
	{
		return new SimpleValueType($args->type);
	}

	/**
	 * @param InstanceArgs $args
	 */
	public function getExpectedInputType(Args $args, TypeContext $context): Node
	{
		return new SimpleNode($args->type);
	}

	/**
	 * @param InstanceArgs $args
	 */
	public function getReturnType(Args $args, TypeContext $context): Node
	{
		return $this->getExpectedInputType($args, $context);
	}

}
