<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Rules\RuleManager;
use Orisai\ObjectMapper\ValueObject;
use Orisai\Utils\Arrays\ArrayMerger;
use ReflectionClass;
use function class_exists;

class MetaLoader
{

	/** @var array<class-string<ValueObject>, Meta> */
	protected array $arrayCache;

	protected MetaCache $cache;

	protected MetaSourceManager $sourceManager;

	protected RuleManager $ruleManager;

	private MetaResolverFactory $resolverFactory;

	protected ?MetaResolver $resolver = null;

	public function __construct(
		MetaCache $cache,
		MetaSourceManager $sourceManager,
		RuleManager $ruleManager,
		MetaResolverFactory $resolverFactory
	)
	{
		$this->cache = $cache;
		$this->sourceManager = $sourceManager;
		$this->ruleManager = $ruleManager;
		$this->resolverFactory = $resolverFactory;
	}

	/**
	 * @param class-string<ValueObject> $class
	 */
	public function load(string $class): Meta
	{
		if (isset($this->arrayCache[$class])) {
			return $this->arrayCache[$class];
		}

		$meta = $this->cache->load($class);

		if ($meta !== null) {
			return $this->arrayCache[$class] = Meta::fromArray($meta);
		}

		if (!class_exists($class)) {
			throw InvalidArgument::create()
				->withMessage("Class '$class' does not exist");
		}

		$classRef = new ReflectionClass($class);

		if (!$classRef->isSubclassOf(ValueObject::class)) {
			$valueObjectClass = ValueObject::class;

			throw InvalidArgument::create()
				->withMessage("Class '$class' should be subclass of '$valueObjectClass'.");
		}

		if (!$classRef->isInstantiable()) {
			throw InvalidArgument::create()
				->withMessage("Class '$class' must be instantiable.");
		}

		$meta = [];

		foreach ($this->sourceManager->getAll() as $source) {
			$sourceMeta = $source->load($classRef);
			// TODO - merge source parts individually - default value, rule, callbacks, ... have different merging rules
			//		- each source should be valid structurally (MetaResolver without resolveArgs calls)
			//		- then be merged with previous source
			//		- and validated that rule/modifier/callback/doc args in merged form are valid
			$meta = ArrayMerger::merge($sourceMeta, $meta);
		}

		$meta = $this->getResolver()->resolve($classRef, $meta);

		$this->cache->save($class, $meta);

		return $this->arrayCache[$class] = Meta::fromArray($meta);
	}

	protected function getResolver(): MetaResolver
	{
		if ($this->resolver === null) {
			$this->resolver = $this->resolverFactory->create($this);
		}

		return $this->resolver;
	}

}
