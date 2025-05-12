<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Compile;

use Orisai\ObjectMapper\Meta\MetaDefinition;

/**
 * @internal
 */
abstract class NodeCompileMeta
{

	/** @var list<MetaDefinition> */
	private array $definitions;

	/**
	 * @param list<MetaDefinition> $definitions
	 */
	public function __construct(array $definitions)
	{
		$this->definitions = $definitions;
	}

	/**
	 * @return list<MetaDefinition>
	 */
	public function getDefinitions(): array
	{
		return $this->definitions;
	}

}
