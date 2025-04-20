<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Source;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Message;
use Orisai\ObjectMapper\Callbacks\CallbackDefinition;
use Orisai\ObjectMapper\Docs\DocDefinition;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Compile\CallbackCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\FieldCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\ModifierCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\RuleCompileMeta;
use Orisai\ObjectMapper\Meta\MetaDefinition;
use Orisai\ObjectMapper\Meta\Shared\DocMeta;
use Orisai\ObjectMapper\Modifiers\ModifierDefinition;
use Orisai\ObjectMapper\Rules\RuleDefinition;
use Orisai\ReflectionMeta\Reader\MetaReader;
use Orisai\ReflectionMeta\Structure\Structure;
use Orisai\ReflectionMeta\Structure\StructureGroup;
use Orisai\SourceMap\AboveReflectorSource;
use Orisai\SourceMap\ReflectorSource;
use ReflectionClass;
use function get_class;
use function sprintf;
use const PHP_VERSION_ID;

/**
 * @internal
 */
abstract class ReflectorMetaSource implements MetaSource
{

	private MetaReader $reader;

	public function __construct(MetaReader $reader)
	{
		$this->reader = $reader;
	}

	public function load(ReflectionClass $rootClass, StructureGroup $group): CompileMeta
	{
		$this->checkUnsupportedReflectors($rootClass, $group);

		$sources = [];
		foreach ($group->getClasses() as $structure) {
			$sources[] = $structure->getSource();
		}

		return new CompileMeta(
			$this->loadClassMeta($rootClass, $group),
			$this->loadPropertiesMeta($group),
			$sources,
			$this->getSourceName(),
			$this->getAnyOfSourceKey(),
			$this->getAllOfSourceKey(),
		);
	}

	/**
	 * @param ReflectionClass<covariant MappedObject> $rootClass
	 */
	private function checkUnsupportedReflectors(ReflectionClass $rootClass, StructureGroup $group): void
	{
		foreach ($group->getGroupedConstants() as $groupedConstant) {
			foreach ($groupedConstant as $constantStructure) {
				$reflector = $constantStructure->getSource()->getReflector();
				$definitions = $this->reader->readConstant($reflector, MetaDefinition::class);

				if ($definitions !== []) {
					$this->throwUnsupportedReflector($rootClass, $constantStructure, 'constants');
				}
			}
		}

		foreach ($group->getGroupedMethods() as $groupedMethod) {
			foreach ($groupedMethod as $methodStructure) {
				$reflector = $methodStructure->getSource()->getReflector();
				$definitions = $this->reader->readMethod($reflector, MetaDefinition::class);

				if ($definitions !== []) {
					$this->throwUnsupportedReflector($rootClass, $methodStructure, 'methods');
				}

				foreach ($methodStructure->getParameters() as $parameterStructure) {
					$parameterReflector = $parameterStructure->getSource()->getReflector();

					// Promoted properties from constructor are duplicated
					if (PHP_VERSION_ID >= 8_00_00 && $parameterReflector->isPromoted()) {
						continue;
					}

					$parameterDefinitions = $this->reader->readParameter($parameterReflector, MetaDefinition::class);
					if ($parameterDefinitions !== []) {
						$this->throwUnsupportedReflector($rootClass, $parameterStructure, 'parameters');
					}
				}
			}
		}
	}

	/**
	 * @param ReflectionClass<covariant MappedObject> $rootClass
	 * @return never
	 */
	private function throwUnsupportedReflector(ReflectionClass $rootClass, Structure $structure, string $type): void
	{
		$message = Message::create()
			->withContext("Resolving metadata of mapped object '{$rootClass->getName()}'.")
			->withProblem("Definitions are not allowed on $type.")
			->withSolution("Remove definition from '{$structure->getSource()->toString()}'.");

		throw InvalidArgument::create()
			->withMessage($message);
	}

	/**
	 * @param ReflectionClass<covariant MappedObject> $rootClass
	 * @return list<ClassCompileMeta>
	 */
	private function loadClassMeta(ReflectionClass $rootClass, StructureGroup $group): array
	{
		$resolved = [];
		foreach ($group->getClasses() as $class) {
			$reflector = $class->getSource()->getReflector();
			$definitions = $this->reader->readClass($reflector, MetaDefinition::class);

			$callbacks = [];
			$docs = [];
			$modifiers = [];

			foreach ($definitions as $definition) {
				$definition = $this->checkDefinitionType($definition);

				if ($definition instanceof RuleDefinition) {
					$className = $reflector->getName();
					$isRootClass = $rootClass->getName() === $className;

					$message = Message::create()
						->withContext("Resolving metadata of mapped object '{$rootClass->getName()}'.")
						->withProblem(sprintf(
							"Rule definition '%s'%s cannot be used on class, it is only allowed on properties.",
							get_class($definition),
							$isRootClass ? '' : " (used above class '$className')",
						));

					throw InvalidArgument::create()
						->withMessage($message);
				}

				if ($definition instanceof CallbackDefinition) {
					$callbacks[] = new CallbackCompileMeta(
						$definition->getType(),
						$definition->getArgs(),
					);
				} elseif ($definition instanceof DocDefinition) {
					$docs[] = new DocMeta(
						$definition->getType(),
						$definition->getArgs(),
					);
				} else {
					$modifiers[] = new ModifierCompileMeta(
						$definition->getType(),
						$definition->getArgs(),
					);
				}
			}

			if ($callbacks === [] && $docs === [] && $modifiers === []) {
				continue;
			}

			$resolved[] = new ClassCompileMeta($callbacks, $docs, $modifiers, $class);
		}

		return $resolved;
	}

	/**
	 * @return list<non-empty-list<FieldCompileMeta>>
	 */
	private function loadPropertiesMeta(StructureGroup $group): array
	{
		$resolved = [];
		foreach ($group->getGroupedProperties() as $groupedProperty) {
			$resolvedGroup = [];
			foreach ($groupedProperty as $propertyStructure) {
				$reflector = $propertyStructure->getSource()->getReflector();
				$definitions = $this->reader->readProperty($reflector, MetaDefinition::class);

				$callbacks = [];
				$docs = [];
				$modifiers = [];
				$rules = [];

				foreach ($definitions as $definition) {
					$definition = $this->checkDefinitionType($definition);

					if ($definition instanceof RuleDefinition) {
						$rules[] = new RuleCompileMeta(
							$definition->getType(),
							$definition->getArgs(),
						);
					} elseif ($definition instanceof CallbackDefinition) {
						$callbacks[] = new CallbackCompileMeta(
							$definition->getType(),
							$definition->getArgs(),
						);
					} elseif ($definition instanceof DocDefinition) {
						$docs[] = new DocMeta(
							$definition->getType(),
							$definition->getArgs(),
						);
					} else {
						$modifiers[] = new ModifierCompileMeta(
							$definition->getType(),
							$definition->getArgs(),
						);
					}
				}

				if ($rules === [] && $callbacks === [] && $docs === [] && $modifiers === []) {
					continue;
				}

				$resolvedGroup[] = new FieldCompileMeta(
					$callbacks,
					$docs,
					$modifiers,
					$rules,
					$propertyStructure,
				);
			}

			if ($resolvedGroup === []) {
				continue;
			}

			$resolved[] = $resolvedGroup;
		}

		return $resolved;
	}

	/**
	 * @return CallbackDefinition|DocDefinition|ModifierDefinition|RuleDefinition
	 */
	private function checkDefinitionType(MetaDefinition $definition): MetaDefinition
	{
		if (
			!$definition instanceof CallbackDefinition
			&& !$definition instanceof DocDefinition
			&& !$definition instanceof ModifierDefinition
			&& !$definition instanceof RuleDefinition
		) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					"Definition '%s' (subtype of '%s') should implement '%s', '%s', '%s' or '%s'.",
					get_class($definition),
					MetaDefinition::class,
					CallbackDefinition::class,
					DocDefinition::class,
					ModifierDefinition::class,
					RuleDefinition::class,
				));
		}

		return $definition;
	}

	/**
	 * @template T of ReflectorSource
	 * @param T $source
	 * @return AboveReflectorSource<T>
	 */
	abstract protected function wrapSource(ReflectorSource $source): AboveReflectorSource;

	abstract protected function getSourceName(): string;

	abstract protected function getAnyOfSourceKey(): string;

	abstract protected function getAllOfSourceKey(): string;

}
