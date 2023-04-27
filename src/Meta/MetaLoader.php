<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Nette\Loaders\RobotLoader;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;
use Orisai\SourceMap\ClassSource;
use ReflectionClass;
use ReflectionEnum;
use function array_merge;
use function array_unique;
use function array_values;
use function assert;
use function class_exists;
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
		[$meta, $fileDependencies] = $this->createRuntimeMeta($classRef);

		$this->metaCache->save(
			$classRef->getName(),
			$meta,
			$fileDependencies,
		);

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

		// Intentionally not calling isInstantiable() - we are able to skip (private) ctor
		if ($classRef->isAbstract() || $classRef->isInterface() || $classRef->isTrait()) {
			throw InvalidArgument::create()
				->withMessage("Class '$class' must be instantiable.");
		}

		if ($classRef instanceof ReflectionEnum) {
			throw InvalidArgument::create()
				->withMessage("Class '$class' can't be an enum.");
		}

		return $classRef;
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 * @return array{RuntimeMeta, list<string>}
	 */
	private function createRuntimeMeta(ReflectionClass $class): array
	{
		$meta = null;
		$sourcesByMetaSource = [];

		// Current sources replace each other (needed for libs offering both annotations and attributes)
		// Sources modifying previous source are not supported
		foreach ($this->sourceManager->getAll() as $metaSource) {
			$sourceMeta = $metaSource->load($class);
			$sourcesByMetaSource[] = $sourceMeta->getSources();

			if ($sourceMeta->hasAnyAttributes()) {
				$meta = $sourceMeta;

				break;
			}
		}

		if ($meta === null) {
			$meta = new CompileMeta(
				[new ClassCompileMeta([], [], [], $class)],
				[],
				[],
			);
		}

		$fileDependencies = [];
		foreach (array_merge(...$sourcesByMetaSource) as $source) {
			if ($source instanceof ClassSource) {
				$fileName = $source->getReflector()->getFileName();
				if ($fileName === false) {
					continue;
				}

				$fileDependencies[] = $fileName;
			} else {
				$fileDependencies[] = $source->getFullPath();
			}
		}

		return [
			$this->getResolver()->resolve($class, $meta),
			array_values(array_unique($fileDependencies)),
		];
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

			if ($classRef->isAbstract() || $classRef->isInterface()) {
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
