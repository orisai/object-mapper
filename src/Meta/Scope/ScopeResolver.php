<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Scope;

use Exception;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Exceptions\Message;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
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
					$this->checkScopeHandlerCompatibility($definition);
					$scope = $definition->getScope();
					$config = $this->getConfig($definition);

					//TODO - testovat, že target anotace/atributu odpovídá našemu Target
					$targets = $config->targets;
					if (!in_array($target, $targets, true)) {
						//TODO - použitý target není pro tuhle definici povolený její skupinou
						//		- vypsat kde je definice použitá
						throw new Exception('a - ' . $propertyStructure->getSource()->toString());
					}

					$hierarchy = $config->hierarchyPosition;
					//TODO - dovolit první třídu, nejen trait? co když bude property definovaná skrze interface?
					if ($hierarchy === HierarchyPosition::firstType() && $property !== $firstGroupedProperty) {
						throw new Exception(
							'e - ' . $propertyStructure->getSource()->toString() . ' ' . get_class($definition),
						);
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

	private function checkScopeHandlerCompatibility(MetaDefinition $definition): void
	{
		$scope = $definition->getScope();
		$handler = $definition->getHandler();

		if (!is_a($handler, $scope, true)) {
			$message = Message::create()
				->withContext('Resolving definition . ' . get_class($definition) . '.')
				->withProblem("Handler $handler is incompatible with scope $scope.")
				->withSolution('Handler must be either same or a subclass of scope.');

			throw InvalidState::create()
				->withMessage($message);
		}
	}

	private function getConfig(MetaDefinition $definition): ScopeConfig
	{
		$scope = $definition->getScope();
		$config = $this->scopeConfigs[$scope] ?? null;
		if ($config === null) {
			$message = Message::create()
				->withContext('Resolving definition . ' . get_class($definition) . '.')
				->withProblem('No config exists for scope ' . $scope . ' returned by the definition.')
				->withSolution('Add configuration to scope resolver or use an existing scope in the definition.');

			throw InvalidState::create()
				->withMessage($message);
		}

		return $config;
	}

}
