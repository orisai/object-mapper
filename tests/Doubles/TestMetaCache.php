<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\MetaCache;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;

final class TestMetaCache implements MetaCache
{

	/** @var array<class-string<MappedObject>, RuntimeMeta> */
	private array $cache = [];

	/**
	 * @param class-string<MappedObject> $class
	 */
	public function load(string $class): ?RuntimeMeta
	{
		return $this->cache[$class] ?? null;
	}

	/**
	 * @param class-string<MappedObject> $class
	 */
	public function save(string $class, RuntimeMeta $meta): void
	{
		$this->cache[$class] = $meta;
	}

}
