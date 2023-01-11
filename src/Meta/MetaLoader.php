<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Nette\Loaders\RobotLoader;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\NotImplemented;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;
use ReflectionClass;
use function assert;
use function class_exists;
use function count;
use function is_subclass_of;

final class MetaLoader
{

	/** @var array<string, RuntimeMeta> */
	private array $arrayCache;

	private MetaCache $metaCache;

	private MetaSourceManager $sourceManager;

	private MetaResolverFactory $resolverFactory;

	private ?MetaResolver $resolver = null;

	public function __construct(
		MetaCache $metaCache,
		MetaSourceManager $sourceManager,
		MetaResolverFactory $resolverFactory
	)
	{
		$this->metaCache = $metaCache;
		$this->sourceManager = $sourceManager;
		$this->resolverFactory = $resolverFactory;
	}

	public function load(string $class): RuntimeMeta
	{
		if (isset($this->arrayCache[$class])) {
			return $this->arrayCache[$class];
		}

		return $this->arrayCache[$class] = $this->metaCache->load($class)
			?? $this->getRuntimeMeta($class);
	}

	private function getRuntimeMeta(string $class): RuntimeMeta
	{
		$classRef = $this->validateClass($class);
		$meta = $this->createRuntimeMeta($classRef);

		$this->metaCache->save($classRef->getName(), $meta);

		return $meta;
	}

	/**
	 * @return ReflectionClass<MappedObject>
	 */
	private function validateClass(string $class): ReflectionClass
	{
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

		assert(is_subclass_of($class, MappedObject::class));

		if ($classRef->isAbstract() || $classRef->isInterface() || $classRef->isTrait()) {
			throw InvalidArgument::create()
				->withMessage("Class '$class' must be instantiable.");
		}

		return $classRef;
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 */
	private function createRuntimeMeta(ReflectionClass $class): RuntimeMeta
	{
		if (count($this->sourceManager->getAll()) > 1) {
			throw NotImplemented::create()
				->withMessage('Only one source is supported at this moment.');
		}

		$sourceMeta = null;
		foreach ($this->sourceManager->getAll() as $source) {
			$sourceMeta = $source->load($class);
		}

		if ($sourceMeta === null) {
			throw InvalidArgument::create()
				->withMessage("No metadata for class {$class->getName()}");
		}

		return $this->getResolver()->resolve($class, $sourceMeta);
	}

	/**
	 * @param list<string> $paths
	 */
	public function preloadFromPaths(array $paths): void
	{
		$loader = new RobotLoader();
		foreach ($paths as $path) {
			$loader->addDirectory($path);
		}

		$loader->rebuild();

		foreach ($loader->getIndexedClasses() as $class => $file) {
			assert(class_exists($class));
			$classRef = new ReflectionClass($class);

			if (!$classRef->isSubclassOf(MappedObject::class)) {
				continue;
			}

			assert(is_subclass_of($class, MappedObject::class));

			if ($classRef->isAbstract() || $classRef->isInterface() || $classRef->isTrait()) {
				continue;
			}

			$this->load($class);
		}
	}

	private function getResolver(): MetaResolver
	{
		if ($this->resolver === null) {
			$this->resolver = $this->resolverFactory->create($this);
		}

		return $this->resolver;
	}

}
