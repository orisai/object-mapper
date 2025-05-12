<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

/**
 * @internal
 */
interface MetaDefinition
{

	//TODO - předefinovat skupiny, otestovat implementace

	/**
	 * @return class-string
	 */
	public function getScope(): string;

	/**
	 * @return class-string
	 */
	public function getHandler(): string;

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array;

}
