<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Types\CompoundType;
use Orisai\ObjectMapper\Types\CompoundTypeOperator;
use Orisai\ObjectMapper\Types\SimpleValueType;
use function is_scalar;

/**
 * @implements Rule<EmptyArgs>
 */
final class ScalarRule implements Rule
{

	use NoArgsRule;

	/**
	 * @param mixed $value
	 * @param EmptyArgs $args
	 * @return int|float|string|bool
	 * @throws ValueDoesNotMatch
	 */
	public function processValue(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	)
	{
		if (!is_scalar($value)) {
			$type = $this->createType($args, $services, $dynamic);
			foreach ($this->getSubtypes() as $key => $subtype) {
				$type->overwriteInvalidSubtype(
					$key,
					ValueDoesNotMatch::create($subtype, Value::none()),
				);
			}

			throw ValueDoesNotMatch::create($type, Value::of($value));
		}

		return $value;
	}

	public function createType(
		Args $args,
		ServicesContext $services,
		DynamicContext $dynamic
	): CompoundType
	{
		$type = new CompoundType(CompoundTypeOperator::or());

		foreach ($this->getSubtypes() as $key => $subtype) {
			$type->addSubtype($key, $subtype);
		}

		return $type;
	}

	/**
	 * @return array<SimpleValueType>
	 */
	private function getSubtypes(): array
	{
		return [
			new SimpleValueType('int'),
			new SimpleValueType('float'),
			new SimpleValueType('string'),
			new SimpleValueType('bool'),
		];
	}

}
