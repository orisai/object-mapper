<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\Meta\Args;
use Orisai\ObjectMapper\Meta\ArgsChecker;
use Orisai\ObjectMapper\Meta\ArgsCreator;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ObjectMapper\Types\StructureType;
use Orisai\ObjectMapper\ValueObject;
use function array_keys;

/**
 * @implements Rule<StructureArgs>
 */
final class StructureRule implements Rule
{

	use ArgsCreator;

	public const TYPE = 'type';

	/**
	 * @param array<mixed> $args
	 * @return array<mixed>
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): array
	{
		$checker = new ArgsChecker($args, self::class);

		$checker->checkAllowedArgs([self::TYPE]);

		$checker->checkRequiredArg(self::TYPE);
		$checker->checkString(self::TYPE);

		// Load structure to ensure whole hierarchy is valid even if not used
		// Note: Loading as class should be always array cached and in runtime should be metadata resolved only once so it has no performance impact
		$context->getMetaLoader()->load($args[self::TYPE]);

		return $args;
	}

	public function getArgsType(): string
	{
		return StructureArgs::class;
	}

	/**
	 * @param mixed $value
	 * @param StructureArgs $args
	 * @return ValueObject|array<mixed>
	 * @throws InvalidData
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		$processor = $context->getProcessor();

		return $context->isInitializeObjects()
			? $processor->process($value, $args->type, $context->getOptions())
			: $processor->processWithoutInitialization($value, $args->type, $context->getOptions());
	}

	/**
	 * @param StructureArgs $args
	 */
	public function createType(Args $args, TypeContext $context): StructureType
	{
		$propertiesMeta = $context->getMeta($args->type)->getProperties();
		/** @var array<string> $propertyNames */
		$propertyNames = array_keys($propertiesMeta);

		$type = new StructureType($args->type);

		foreach ($propertyNames as $propertyName) {
			$propertyMeta = $propertiesMeta[$propertyName];
			$propertyRuleMeta = $propertyMeta->getRule();
			$propertyRule = $context->getRule($propertyRuleMeta->getType());
			$propertyArgs = $this->createRuleArgsInst($propertyRule, $propertyRuleMeta);

			$fieldNameMeta = $propertyMeta->getModifier(FieldNameModifier::class);
			$fieldName = $fieldNameMeta !== null
				? $fieldNameMeta->getArgs()[FieldNameModifier::NAME]
				: $propertyName;

			$type->addField(
				$fieldName,
				$propertyRule->createType($propertyArgs, $context),
			);
		}

		return $type;
	}

}
