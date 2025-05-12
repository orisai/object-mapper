<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Source;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Message;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\FieldCompileMeta;
use Orisai\ObjectMapper\Meta\MetaDefinition;
use Orisai\ReflectionMeta\Reader\MetaReader;
use Orisai\ReflectionMeta\Structure\Structure;
use Orisai\ReflectionMeta\Structure\StructureGroup;
use Orisai\SourceMap\AboveReflectorSource;
use Orisai\SourceMap\ReflectorSource;
use ReflectionClass;
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
			$this->loadClasses($group),
			$this->loadGroupedProperties($group),
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
			->withContext("Resolving metadata of '{$rootClass->getName()}'.")
			->withProblem("Definitions are not allowed on $type.")
			->withSolution("Remove definition from '{$structure->getSource()->toString()}'.");

		throw InvalidArgument::create()
			->withMessage($message);
	}

	/**
	 * @return non-empty-list<ClassCompileMeta>
	 */
	private function loadClasses(StructureGroup $group): array
	{
		$resolved = [];
		foreach ($group->getClasses() as $class) {
			$reflector = $class->getSource()->getReflector();
			$definitions = $this->reader->readClass($reflector, MetaDefinition::class);

			$resolved[] = new ClassCompileMeta($definitions, $class);
		}

		return $resolved;
	}

	/**
	 * @return array<string, non-empty-list<FieldCompileMeta>>
	 */
	private function loadGroupedProperties(StructureGroup $group): array
	{
		$resolved = [];
		foreach ($group->getGroupedProperties() as $propertyName => $groupedProperty) {
			$resolvedGroup = [];
			foreach ($groupedProperty as $propertyStructure) {
				$reflector = $propertyStructure->getSource()->getReflector();
				$definitions = $this->reader->readProperty($reflector, MetaDefinition::class);
				$resolvedGroup[] = new FieldCompileMeta($definitions, $propertyStructure);
			}

			if ($resolvedGroup === []) {
				continue;
			}

			$resolved[$propertyName] = $resolvedGroup;
		}

		return $resolved;
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
