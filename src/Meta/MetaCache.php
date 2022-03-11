<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\ObjectMapper\MappedObject;

interface MetaCache
{

	/**
	 * @param class-string<MappedObject> $class
	 * @return array<mixed>|null
	 */
	public function load(string $class): ?array;

	/**
	 * @param class-string<MappedObject> $class
	 * @param array<mixed>               $meta
	 */
	public function save(string $class, array $meta): void;

}
