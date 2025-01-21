<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Context\MetaFieldContext;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Types\SimpleValueType;
use function class_exists;
use function interface_exists;
use function is_string;

/**
 * @implements Rule<InstanceOfArgs>
 */
final class InstanceOfRule implements Rule
{

	public const Type = 'type';

	public function resolveArgs(array $args, MetaFieldContext $context): InstanceOfArgs
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

		return new InstanceOfArgs($type);
	}

	public function getArgsType(): string
	{
		return InstanceOfArgs::class;
	}

	/**
	 * @param mixed          $value
	 * @param InstanceOfArgs $args
	 * @throws ValueDoesNotMatch
	 */
	public function processValue(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	): object
	{
		if ($value instanceof $args->type) {
			return $value;
		}

		throw ValueDoesNotMatch::create($this->createType($args, $services, $dynamic), Value::of($value));
	}

	public function createType(
		Args $args,
		ServicesContext $services,
		DynamicContext $dynamic
	): SimpleValueType
	{
		return new SimpleValueType($args->type);
	}

}
