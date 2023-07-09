<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Nette\Loaders\RobotLoader;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Message;
use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Meta\Cache\MetaCache;
use Orisai\ObjectMapper\Meta\Compile\ClassCompileMeta;
use Orisai\ObjectMapper\Meta\Compile\CompileMeta;
use Orisai\ObjectMapper\Meta\Runtime\RuntimeMeta;
use Orisai\ObjectMapper\Meta\Source\MetaSourceManager;
use Orisai\ReflectionMeta\Structure\ClassStructure;
use Orisai\SourceMap\ClassSource;
use ReflectionClass;
use ReflectionException;
use UnitEnum;
use function array_merge;
use function array_unique;
use function array_values;
use function assert;
use function class_exists;
use function interface_exists;
use function is_subclass_of;
use function trait_exists;
use const PHP_VERSION_ID;

final class MetaLoader
{

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

	/**
	 * @param class-string<MappedObject> $class
	 */
	public function load(string $class): RuntimeMeta
	{
		return $this->metaCache->load($class)
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
		try {
			/** @phpstan-ignore-next-line In case object is not a class, ReflectionException is thrown */
			$reflector = new ReflectionClass($class);
		} catch (ReflectionException $exception) {
			throw InvalidArgument::create()
				->withMessage("Class '$class' does not exist.");
		}

		if (!$reflector->isSubclassOf(MappedObject::class)) {
			$mappedObjectClass = MappedObject::class;

			$message = Message::create()
				->withContext("Resolving metadata of mapped object '$class'.")
				->withProblem('Class does not implement interface of mapped object.')
				->withSolution("Implement the '$mappedObjectClass' interface.");

			throw InvalidArgument::create()
				->withMessage($message);
		}

		if ($reflector->isInterface()) {
			$message = Message::create()
				->withContext("Resolving metadata of mapped object '$class'.")
				->withProblem("'$class' is an interface.")
				->withSolution('Load metadata only for classes.');

			throw InvalidArgument::create()
				->withMessage($message);
		}

		if ($reflector->isAbstract()) {
			$message = Message::create()
				->withContext("Resolving metadata of mapped object '$class'.")
				->withProblem("'$class' is abstract.")
				->withSolution('Load metadata only for non-abstract classes.');

			throw InvalidArgument::create()
				->withMessage($message);
		}

		if (PHP_VERSION_ID >= 8_01_00 && $reflector->isSubclassOf(UnitEnum::class)) {
			$message = Message::create()
				->withContext("Resolving metadata of mapped object '$class'.")
				->withProblem("Mapped object can't be an enum.");

			throw InvalidArgument::create()
				->withMessage($message);
		}

		return $reflector;
	}

	/**
	 * @param ReflectionClass<MappedObject> $class
	 * @return array{RuntimeMeta, list<string>}
	 */
	private function createRuntimeMeta(ReflectionClass $class): array
	{
		$meta = null;
		$sourcesByMetaSource = [];

		// Current sources replace each other
		// Sources modifying previous source are not supported
		foreach ($this->sourceManager->getAll() as $metaSource) {
			$sourceMeta = $metaSource->load($class);
			$sourcesByMetaSource[] = $sourceMeta->getSources();

			if ($sourceMeta->hasAnyMeta()) {
				$meta = $sourceMeta;

				break;
			}
		}

		if ($meta === null) {
			$meta = new CompileMeta(
				[
					new ClassCompileMeta(
						[],
						[],
						[],
						new ClassStructure($class, new ClassSource($class)),
					),
				],
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
	 * @param list<string> $excludePaths
	 */
	public function preloadFromPaths(array $paths, array $excludePaths = []): void
	{
		$loader = new RobotLoader();

		foreach ($paths as $path) {
			$loader->addDirectory($path);
		}

		foreach ($excludePaths as $excludePath) {
			$loader->excludeDirectory($excludePath);
		}

		$loader->rebuild();

		foreach ($loader->getIndexedClasses() as $class => $file) {
			require_once $file;

			assert(class_exists($class) || interface_exists($class) || trait_exists($class));

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
