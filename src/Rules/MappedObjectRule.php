<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Rules;

use Closure;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Args\ArgsChecker;
use Orisai\ObjectMapper\Exception\InvalidData;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Context\MetaFieldContext;
use Orisai\ObjectMapper\Meta\Runtime\FieldRuntimeMeta;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\PropertyContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Types\MappedObjectType;
use Orisai\ObjectMapper\Types\Type;
use Throwable;
use function array_key_exists;
use function assert;
use function in_array;
use function is_a;

/**
 * @implements Rule<MappedObjectArgs>
 */
final class MappedObjectRule implements Rule
{

	public const ClassName = 'class';

	/** @var array<string, null> */
	private array $resolvedClasses = [];

	public function resolveArgs(array $args, MetaFieldContext $context): MappedObjectArgs
	{
		$checker = new ArgsChecker($args, self::class);

		$checker->checkAllowedArgs([self::ClassName]);

		$checker->checkRequiredArg(self::ClassName);
		$type = $checker->checkString(self::ClassName);

		// Load object to ensure whole hierarchy is valid even if not used
		if (!array_key_exists($type, $this->resolvedClasses)) {
			$this->resolvedClasses[$type] = null;
			try {
				/** @phpstan-ignore-next-line Meta loader validates type */
				$context->getMetaLoader()->load($type);
			} catch (Throwable $e) {
				unset($this->resolvedClasses[$type]);

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
	 * @param mixed $value
	 * @param MappedObjectArgs $args
	 * @return MappedObject|array<mixed>
	 * @throws InvalidData
	 */
	public function processValue(
		$value,
		Args $args,
		ServicesContext $services,
		PropertyContext $property,
		DynamicContext $dynamic
	)
	{
		$processor = $services->getProcessor();

		$options = $dynamic->getOptions()->createClone();

		return $dynamic->shouldInitializeObjects()
			? $processor->process($value, $args->class, $options)
			: $processor->processWithoutMapping($value, $args->class, $options);
	}

	public function createType(
		Args $args,
		ServicesContext $services,
		DynamicContext $dynamic
	): MappedObjectType
	{
		$type = new MappedObjectType($args->class);

		if (in_array($args->class, $dynamic->getProcessedClasses(), true)) {
			return $type;
		}

		foreach ($services->getMeta($args->class)->getFields() as $fieldName => $fieldMeta) {
			$type->addField(
				$fieldName,
				$this->getTypeCreator($fieldMeta, $args, $services, $dynamic),
			);
		}

		return $type;
	}

	/**
	 * @return Closure(): Type
	 */
	private function getTypeCreator(
		FieldRuntimeMeta $fieldMeta,
		MappedObjectArgs $args,
		ServicesContext $services,
		DynamicContext $dynamic
	): Closure
	{
		$fieldRuleMeta = $fieldMeta->getRule();
		$fieldRule = $services->getRule($fieldRuleMeta->getType());
		$fieldArgs = $fieldRuleMeta->getArgs();

		return static fn (): Type => $fieldRule->createType(
			$fieldArgs,
			$services,
			$dynamic->withProcessedClass($args->class),
		);
	}

}
