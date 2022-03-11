<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Doubles;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\MetaCache;

final class TestMetaCache implements MetaCache
{

	/** @var array<class-string<MappedObject>, array<mixed>> */
	private array $cache = [];

	/**
	 * @param class-string<MappedObject> $class
	 * @return array<mixed>|null
	 */
	public function load(string $class): ?array
	{
		return $this->cache[$class] ?? null;
	}

	/**
	 * @param class-string<MappedObject> $class
	 * @param array<mixed>               $meta
	 */
	public function save(string $class, array $meta): void
	{
		$this->cache[$class] = $meta;
	}

}
