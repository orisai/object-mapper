<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;

interface MetaCache
{

	/**
	 * @param class-string<MappedObject> $class
	 */
	public function load(string $class): ?RuntimeMeta;

	/**
	 * @param class-string<MappedObject> $class
	 */
	public function save(string $class, RuntimeMeta $meta): void;

}
