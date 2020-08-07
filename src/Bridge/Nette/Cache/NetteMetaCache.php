<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Bridge\Nette\Cache;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Orisai\ObjectMapper\Meta\ClassModificationsChecker;
use Orisai\ObjectMapper\Meta\MetaCache;
use Orisai\ObjectMapper\ValueObject;

final class NetteMetaCache implements MetaCache
{

	private const NAMESPACE = 'orisai.objectMapper.meta';

	private Cache $cache;

	private bool $debugMode;

	public function __construct(IStorage $storage, bool $debugMode, string $namespace = self::NAMESPACE)
	{
		$this->cache = new Cache($storage, $namespace);
		$this->debugMode = $debugMode;
	}

	/**
	 * @phpstan-param class-string<ValueObject> $class
	 * @return array<mixed>|null
	 */
	public function load(string $class): ?array
	{
		return $this->cache->load($class);
	}

	/**
	 * @phpstan-param class-string<ValueObject> $class
	 * @param array<mixed> $meta
	 */
	public function save(string $class, array $meta): void
	{
		$this->cache->save($class, $meta, $this->getDependencies($class));
	}

	/**
	 * @phpstan-param class-string<ValueObject> $class
	 * @return array<mixed>
	 */
	protected function getDependencies(string $class): array
	{
		return $this->debugMode
			? [Cache::FILES => ClassModificationsChecker::getSourceFiles($class)]
			: [];
	}

}
