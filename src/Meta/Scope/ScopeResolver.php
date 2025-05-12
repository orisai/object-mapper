<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Scope;

use Exception;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Exceptions\Message;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Compile\FieldCompileMeta;
use Orisai\ObjectMapper\Meta\MetaDefinition;
use Orisai\ReflectionMeta\Structure\PropertyStructure;
use function array_key_first;
use function get_class;
use function in_array;
use function is_a;

final class ScopeResolver
{

	/** @var array<class-string, ScopeConfig> */
	private array $scopeConfigs = [];

	/**
	 * @param class-string $scope
	 */
	public function setScopeConfig(string $scope, ScopeConfig $config): void
	{
		if (isset($this->scopeConfigs[$scope])) {
			//TODO - message
			throw new Exception('f - config cannot be changed');
		}

		$this->scopeConfigs[$scope] = $config;
	}

	//TODO
	//	- třídy
	//TODO - strukturu do objektů

	/**
	 * @return array<string, non-empty-array<class-string, array{
	 *     propertyStructure: PropertyStructure,
	 *     scopedDefinitions: array<class-string, list<MetaDefinition>>,
	 * }>>
	 */
	public function resolveProperties(CompileMeta $meta): array
	{
		$target = Target::targetProperty();
		$scopedProperties = [];
		$scopesByProperty = [];
		foreach ($meta->getGroupedProperties() as $propertyName => $groupedProperty) {
			if ($groupedProperty === []) {
				continue;
			}

			$firstGroupedProperty = $groupedProperty[array_key_first($groupedProperty)];
			$scopedProperty = [];
			foreach ($groupedProperty as $property) {
				$propertyStructure = $property->getPropertyStructure();
				$className = $propertyStructure->getSource()->getClass()->getReflector()->getName();
				$scopedDefinitions = [];

				foreach ($property->getDefinitions() as $definition) {
					$scope = $definition->getScope();
					$handler = $definition->getHandler();

					if (!is_a($handler, $scope, true)) {
						$this->throwHandlerIncompatibleWithScope($definition);
					}

					$config = $this->getConfig($definition);

					//TODO - testovat, že target anotace/atributu odpovídá našemu Target
					$targets = $config->targets;
					if (!in_array($target, $targets, true)) {
						$this->throwTargetIsNotAllowed($target, $definition, $propertyStructure);
					}

					$hierarchy = $config->hierarchyPosition;
					//TODO - dovolit první třídu, nejen trait? co když bude property definovaná skrze interface?
					if ($property !== $firstGroupedProperty && $hierarchy === HierarchyPosition::firstType()) {
						$this->throwDefinitionNotUsedAboveFirstOccurrence($definition, $propertyStructure, $target, $firstGroupedProperty);
					}

					$scopesByProperty[$propertyName][$scope] = true;
					$scopedDefinitions[$scope][] = $definition;
				}

				//TODO - tady by se mohl kontrolovat alespoň unique v rámci jedné třídy
				//		- možná by se to mohlo oddělit od logiky RepeatableBehavior? nebo nějak odlišit v ní
				//		- rule musí být unique
				$scopedProperty[$className] = [
					'propertyStructure' => $propertyStructure,
					'scopedDefinitions' => $scopedDefinitions,
				];
			}

			//TODO - předat dál repeatable pro každý scope
			$scopedProperties[$propertyName] = $scopedProperty;
		}

		foreach ($this->scopeConfigs as $scope => $config) {
			if (!$config->required) {
				continue;
			}

			foreach ($scopesByProperty as $propertyName => $scopes) {
				if (!isset($scopes[$scope])) {
					//TODO - definice je vyžadovaná, ale není v property s definicemi dostupná
					throw new Exception('b - required ' . $propertyName);
				}
			}
		}

		return $scopedProperties;
	}

	/**
	 * @return never
	 */
	private function throwHandlerIncompatibleWithScope(MetaDefinition $definition): void
	{
		$className = get_class($definition);
		$scope = $definition->getScope();
		$handler = $definition->getHandler();

		$message = Message::create()
			->withContext("Resolving definition '$className'.")
			->withProblem("Handler '$handler' is incompatible with scope '$scope'.")
			->withSolution('Handler must be either same or a subclass of scope.');

		throw InvalidState::create()
			->withMessage($message);
	}

	private function getConfig(MetaDefinition $definition): ScopeConfig
	{
		$scope = $definition->getScope();
		$config = $this->scopeConfigs[$scope] ?? null;
		if ($config === null) {
			$className = get_class($definition);
			$message = Message::create()
				->withContext("Resolving definition '$className'.")
				->withProblem("No config exists for scope '$scope' returned by the definition.")
				->withSolution('Add configuration to scope resolver or use an existing scope in the definition.');

			throw InvalidState::create()
				->withMessage($message);
		}

		return $config;
	}

	/**
	 * @return never
	 */
	private function throwTargetIsNotAllowed(
		Target $target,
		MetaDefinition $definition,
		PropertyStructure $propertyStructure
	): void
	{
		$className = get_class($definition);
		$message = Message::create()
			->withContext("Resolving metadata of '{$propertyStructure->getSource()->toString()}'.")
			->withProblem("Used definition '$className' does not allow to be used on '$target->value'.");

		throw InvalidState::create()
			->withMessage($message);
	}

	/**
	 * @return never
	 */
	private function throwDefinitionNotUsedAboveFirstOccurrence(
		MetaDefinition $definition,
		PropertyStructure $propertyStructure,
		Target $target,
		FieldCompileMeta $firstGroupedProperty
	): void
	{
		$className = get_class($definition);

		$message = Message::create()
			->withContext("Resolving metadata of '{$propertyStructure->getSource()->toString()}'.")
			->withProblem(
				"Used definition '$className' can be used only on the first '$target->value' occurrence,"
				. " '{$firstGroupedProperty->getPropertyStructure()->getSource()->toString()}'."
			);

		throw InvalidState::create()
			->withMessage($message);
	}

}
