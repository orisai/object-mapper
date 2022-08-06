<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\NotImplemented;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;
use ReflectionClass;
use function class_exists;
use function count;

final class MetaLoader
{

	/** @var array<class-string<MappedObject>, RuntimeMeta> */
	private array $arrayCache;

	private MetaCache $cache;

	private MetaSourceManager $sourceManager;

	private MetaResolverFactory $resolverFactory;

	private ?MetaResolver $resolver = null;

	public function __construct(
		MetaCache $cache,
		MetaSourceManager $sourceManager,
		MetaResolverFactory $resolverFactory
	)
	{
		$this->cache = $cache;
		$this->sourceManager = $sourceManager;
		$this->resolverFactory = $resolverFactory;
	}

	/**
	 * @param class-string<MappedObject> $class
	 */
	public function load(string $class): RuntimeMeta
	{
		if (isset($this->arrayCache[$class])) {
			return $this->arrayCache[$class];
		}

		$meta = $this->cache->load($class);

		if ($meta !== null) {
			return $this->arrayCache[$class] = $meta;
		}

		if (!class_exists($class)) {
			throw InvalidArgument::create()
				->withMessage("Class '$class' does not exist");
		}

		$classRef = new ReflectionClass($class);

		if (!$classRef->isSubclassOf(MappedObject::class)) {
			$mappedObjectClass = MappedObject::class;

			throw InvalidArgument::create()
				->withMessage("Class '$class' should be subclass of '$mappedObjectClass'.");
		}

		if (!$classRef->isInstantiable()) {
			throw InvalidArgument::create()
				->withMessage("Class '$class' must be instantiable.");
		}

		if (count($this->sourceManager->getAll()) > 1) {
			throw NotImplemented::create()
				->withMessage('Only one source is supported at this moment.');
		}

		$sourceMeta = null;
		foreach ($this->sourceManager->getAll() as $source) {
			$sourceMeta = $source->load($classRef);
		}

		if ($sourceMeta === null) {
			throw InvalidArgument::create()
				->withMessage("No metadata for class $class");
		}

		$meta = $this->getResolver()->resolve($classRef, $sourceMeta);

		$this->cache->save($class, $meta);

		return $this->arrayCache[$class] = $meta;
	}

	private function getResolver(): MetaResolver
	{
		if ($this->resolver === null) {
			$this->resolver = $this->resolverFactory->create($this);
		}

		return $this->resolver;
	}

}
