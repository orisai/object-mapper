<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\EmptyArgs;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Rules\MultiValueEfficientRule;
use Orisai\ObjectMapper\Rules\NoArgsRule;
use Orisai\ObjectMapper\Types\SimpleValueType;

/**
 * @implements MultiValueEfficientRule<EmptyArgs>
 */
final class EfficientTestRule implements MultiValueEfficientRule
{

	use NoArgsRule;

	public const Fail1 = 'fail1',
		Fail3 = 'fail3';

	/** @var array<mixed> */
	public array $calls = [];

	public function processValue($value, Args $args, FieldContext $context)
	{
		$this->addCall($value, 0);
		$processed1 = $this->processValuePhase1($value, $args, $context);

		return $this->processValuePhase3($processed1, $args, $context);
	}

	public function processValuePhase1($value, Args $args, FieldContext $context)
	{
		$this->addCall($value, 1);

		if ($value === self::Fail1) {
			throw ValueDoesNotMatch::create(
				$this->createType($args, $context),
				Value::of($value),
			);
		}

		return $value;
	}

	public function processValuePhase2(array $values, Args $args, FieldContext $context): void
	{
		$this->addCall($values, 2);
	}

	public function processValuePhase3($value, Args $args, FieldContext $context)
	{
		$this->addCall($value, 3);

		if ($value === self::Fail3) {
			throw ValueDoesNotMatch::create(
				$this->createType($args, $context),
				Value::of($value),
			);
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 */
	private function addCall($value, int $phase): void
	{
		$this->calls[] = [
			'phase' => $phase,
			'value' => $value,
		];
	}

	public function createType(Args $args, TypeContext $context): SimpleValueType
	{
		return new SimpleValueType('efficient');
	}

}
