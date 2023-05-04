<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

/**
 * @internal
 */
interface MetaDefinition
{

	/**
	 * @return class-string
	 */
	public function getType(): string;

	/**
	 * @return array<mixed>
	 */
	public function getArgs(): array;

}
