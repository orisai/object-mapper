<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Cache;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;

final class ArrayMetaCache implements MetaCache
{

	/** @var array<class-string<MappedObject>, RuntimeMeta> */
	private array $cache = [];

	public function load(string $class): ?RuntimeMeta
	{
		return $this->cache[$class] ?? null;
	}

	public function save(string $class, RuntimeMeta $meta, array $fileDependencies): void
	{
		$this->cache[$class] = $meta;
	}

}
