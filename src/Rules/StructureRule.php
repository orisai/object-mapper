<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exceptions\InvalidData;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Modifiers\FieldNameArgs;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ObjectMapper\Types\StructureType;
use function array_keys;
use function assert;
use function is_a;

/**
 * @phpstan-implements Rule<StructureArgs>
 */
final class StructureRule implements Rule
{

	public const TYPE = 'type';

	/**
	 * {@inheritDoc}
	 */
	public function resolveArgs(array $args, RuleArgsContext $context): StructureArgs
	{
		$checker = new ArgsChecker($args, self::class);

		$checker->checkAllowedArgs([self::TYPE]);

		$checker->checkRequiredArg(self::TYPE);
		$type = $checker->checkString(self::TYPE);

		// Load structure to ensure whole hierarchy is valid even if not used
		// Note: Loading as class should be always array cached and in runtime should be metadata resolved only once so it has no performance impact
		$context->getMetaLoader()->load($args[self::TYPE]);
		assert(is_a($type, MappedObject::class, true));

		return new StructureArgs($type);
	}

	public function getArgsType(): string
	{
		return StructureArgs::class;
	}

	/**
	 * @param mixed         $value
	 * @param StructureArgs $args
	 * @return MappedObject|array<mixed>
	 * @throws InvalidData
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		$processor = $context->getProcessor();

		return $context->shouldMapDataToObjects()
			? $processor->process($value, $args->type, $context->getOptions())
			: $processor->processWithoutMapping($value, $args->type, $context->getOptions());
	}

	/**
	 * @param StructureArgs $args
	 */
	public function createType(Args $args, TypeContext $context): StructureType
	{
		$propertiesMeta = $context->getMeta($args->type)->getProperties();
		$propertyNames = array_keys($propertiesMeta);

		$type = new StructureType($args->type);

		foreach ($propertyNames as $propertyName) {
			$propertyMeta = $propertiesMeta[$propertyName];
			$propertyRuleMeta = $propertyMeta->getRule();
			$propertyRule = $context->getRule($propertyRuleMeta->getType());
			$propertyArgs = $propertyRuleMeta->getArgs();

			$fieldNameMeta = $propertyMeta->getModifier(FieldNameModifier::class);
			if ($fieldNameMeta !== null) {
				$args = $fieldNameMeta->getArgs();
				assert($args instanceof FieldNameArgs);
				$fieldName = $args->name;
			} else {
				$fieldName = $propertyName;
			}

			$type->addField(
				$fieldName,
				$propertyRule->createType($propertyArgs, $context),
			);
		}

		return $type;
	}

}
