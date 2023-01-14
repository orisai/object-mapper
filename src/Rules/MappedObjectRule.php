<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Closure;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Context\FieldContext;
use Orisai\ObjectMapper\Context\RuleArgsContext;
use Orisai\ObjectMapper\Context\TypeContext;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Runtime\PropertyRuntimeMeta;
use Orisai\ObjectMapper\Modifiers\FieldNameModifier;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\Type;
use Throwable;
use function array_key_exists;
use function assert;
use function in_array;
use function is_a;

/**
 * @phpstan-implements Rule<MappedObjectArgs>
 */
final class MappedObjectRule implements Rule
{

	private const Type = 'type';

	/** @var array<string, null> */
	private array $alreadyResolved = [];

	public function resolveArgs(array $args, RuleArgsContext $context): MappedObjectArgs
	{
		$checker = new ArgsChecker($args, self::class);

		$checker->checkAllowedArgs([self::Type]);

		$checker->checkRequiredArg(self::Type);
		$type = $checker->checkString(self::Type);

		// Load object to ensure whole hierarchy is valid even if not used
		if (!array_key_exists($type, $this->alreadyResolved)) {
			$this->alreadyResolved[$type] = null;
			try {
				$context->getMetaLoader()->load($type);
			} catch (Throwable $e) {
				unset($this->alreadyResolved[$type]);

				throw $e;
			}
		}

		assert(is_a($type, MappedObject::class, true));

		return new MappedObjectArgs($type);
	}

	public function getArgsType(): string
	{
		return MappedObjectArgs::class;
	}

	/**
	 * @param mixed            $value
	 * @param MappedObjectArgs $args
	 * @return MappedObject|array<mixed>
	 * @throws InvalidData
	 */
	public function processValue($value, Args $args, FieldContext $context)
	{
		$processor = $context->getProcessor();

		$options = $context->getOptions()->createClone();
		foreach ($context->getProcessedClasses() as $class) {
			$options = $options->withProcessedClass($class);
		}

		return $context->shouldMapDataToObjects()
			? $processor->process($value, $args->type, $options)
			: $processor->processWithoutMapping($value, $args->type, $options);
	}

	/**
	 * @param MappedObjectArgs $args
	 */
	public function createType(Args $args, TypeContext $context): MappedObjectType
	{
		if (in_array($args->type, $context->getProcessedClasses(), true)) {
			return new MappedObjectType($args->type);
		}

		$type = new MappedObjectType($args->type);
		foreach ($context->getMeta($args->type)->getProperties() as $propertyName => $propertyMeta) {
			$type->addField(
				$this->getFieldName($propertyMeta, $propertyName),
				$this->getTypeCreator($propertyMeta, $context, $args),
			);
		}

		return $type;
	}

	/**
	 * @return int|string
	 */
	private function getFieldName(PropertyRuntimeMeta $propertyMeta, string $propertyName)
	{
		$fieldNameMeta = $propertyMeta->getModifier(FieldNameModifier::class);

		return $fieldNameMeta !== null ? $fieldNameMeta->getArgs()->name : $propertyName;
	}

	/**
	 * @return Closure(): Type
	 */
	private function getTypeCreator(
		PropertyRuntimeMeta $propertyMeta,
		TypeContext $context,
		MappedObjectArgs $args
	): Closure
	{
		$propertyRuleMeta = $propertyMeta->getRule();
		$propertyRule = $context->getRule($propertyRuleMeta->getType());
		$propertyArgs = $propertyRuleMeta->getArgs();

		return static fn (): Type => $propertyRule->createType(
			$propertyArgs,
			$context->withProcessedClass($args->type),
		);
	}

}
