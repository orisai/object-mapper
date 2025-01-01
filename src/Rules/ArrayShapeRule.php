<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Nette\Utils\Helpers;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\ArgsFieldContext;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Exception\ValueDoesNotMatch;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Processing\Value;
use Orisai\ObjectMapper\Types\ArrayShapeType;
use Orisai\ObjectMapper\Types\MessageType;
use function array_key_exists;
use function array_keys;
use function array_map;
use function is_array;

/**
 * @implements Rule<ArrayShapeArgs>
 */
final class ArrayShapeRule implements Rule
{

	public const Fields = 'fields';

	public function resolveArgs(array $args, ArgsFieldContext $context): ArrayShapeArgs
	{
		$checker = new ArgsChecker($args, self::class);
		$checker->checkAllowedArgs([self::Fields]);

		$checker->checkRequiredArg(self::Fields);
		$fields = $checker->checkArray(self::Fields);

		$resolver = $context->getMetaResolver();

		foreach ($fields as $key => $rule) {
			if (!$rule instanceof RuleCompileMeta) {
				throw InvalidArgument::create();
			}

			$fields[$key] = $resolver->resolveRuleMeta($rule, $context);
		}

		return new ArrayShapeArgs($fields);
	}

	public function getArgsType(): string
	{
		return ArrayShapeArgs::class;
	}

	/**
	 * @return array<int|string, mixed>
	 */
	public function processValue($value, Args $args, FieldContext $context): array
	{
		if (!is_array($value)) {
			$type = $this->createType($args, $context);
			$type->markInvalid();

			throw ValueDoesNotMatch::create($type, Value::of($value));
		}

		$fields = $args->fields;
		$fieldNames = array_keys($fields);
		$type = null;

		// Missing fields
		foreach ($fields as $fieldName => $fieldRuleMeta) {
			if (array_key_exists($fieldName, $value)) {
				continue;
			}

			$fieldRule = $context->getRule($fieldRuleMeta->getType());

			$type ??= $this->createType($args, $context);
			$type->overwriteInvalidField(
				$fieldName,
				ValueDoesNotMatch::create(
					$fieldRule->createType(
						$fieldRuleMeta->getArgs(),
						$context->createClone(),
					),
					Value::none(),
				),
			);
		}

		// Sent fields
		foreach ($value as $fieldName => $fieldValue) {
			$fieldRuleMeta = $fields[$fieldName] ?? null;

			// Unknown field
			if ($fieldRuleMeta === null) {
				unset($value[$fieldName]);

				$hintedFieldName = Helpers::getSuggestion(
					array_map(static fn ($fieldName) => (string) $fieldName, $fieldNames),
					(string) $fieldName,
				);
				$hint = $hintedFieldName !== null && !array_key_exists($hintedFieldName, $value)
					? ", did you mean '$hintedFieldName'?"
					: '.';

				$type ??= $this->createType($args, $context);
				$type->overwriteInvalidField(
					$fieldName,
					ValueDoesNotMatch::create(
						new MessageType("Field is unknown$hint"),
						Value::of($fieldValue),
					),
				);

				continue;
			}

			$fieldRule = $context->getRule($fieldRuleMeta->getType());
			$fieldArgs = $fieldRuleMeta->getArgs();

			try {
				$fieldValue = $fieldRule->processValue(
					$fieldValue,
					$fieldArgs,
					$context->createClone(),
				);
				$value[$fieldName] = $fieldValue;
			} catch (ValueDoesNotMatch | InvalidData $exception) {
				unset($value[$fieldName]);

				$type ??= $this->createType($args, $context);
				$type->overwriteInvalidField($fieldName, $exception);
			}
		}

		if ($type !== null && ($type->hasInvalidFields() || $type->hasErrors())) {
			throw ValueDoesNotMatch::create($type, Value::none());
		}

		return $value;
	}

	public function createType(Args $args, TypeContext $context): ArrayShapeType
	{
		$type = new ArrayShapeType();
		foreach ($args->fields as $fieldName => $fieldRuleMeta) {
			$fieldRule = $context->getRule($fieldRuleMeta->getType());
			$fieldArgs = $fieldRuleMeta->getArgs();

			$fieldType = $fieldRule->createType($fieldArgs, $context);

			$type->addField($fieldName, $fieldType);
		}

		return $type;
	}

}
