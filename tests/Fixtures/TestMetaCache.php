<?php declare(strict_types = 1);

namespace Tests\Orisai\ObjectMapper\Fixtures;

use Orisai\ObjectMapper\Meta\MetaCache;
use Orisai\ObjectMapper\ValueObject;

final class TestMetaCache implements MetaCache
{

	/** @var array<class-string<ValueObject>, array<mixed>> */
	private array $cache = [];

	/**
	 * @phpstan-param class-string<ValueObject> $class
	 * @return array<mixed>|null
	 */
	public function load(string $class): ?array
	{
		return $this->cache[$class] ?? null;
	}

	/**
	 * @phpstan-param class-string<ValueObject> $class
	 * @param array<mixed> $meta
	 */
	public function save(string $class, array $meta): void
	{
		$this->cache[$class] = $meta;
	}

}
