<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

use Contributte\Utils\Merger;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\ObjectMapper\Rules\RuleManager;
use Orisai\ObjectMapper\ValueObject;
use ReflectionClass;
use function class_exists;
use function sprintf;

class MetaLoader
{

	/**
	 * @var array<Meta>
	 * @phpstan-var array<class-string<ValueObject>, Meta>
	 */
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
	 * @phpstan-param class-string<ValueObject> $class
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
				->withMessage(sprintf('Class "%s" does not exist', $class));
		}

		$classRef = new ReflectionClass($class);

		if (!$classRef->isSubclassOf(ValueObject::class) || $classRef->isAbstract()) {
			throw InvalidArgument::create()
				->withMessage(sprintf(
					'Class "%s" should be non-abstract subclass of "%s"',
					$class,
					ValueObject::class,
				));
		}

		$meta = [];

		foreach ($this->sourceManager->getAll() as $source) {
			$sourceMeta = $source->load($classRef);
			// TODO - merge source parts individually - default value, rule, callbacks, ... have different merging rules
			//		- each source should be valid structurally (MetaResolver without resolveArgs calls)
			//		- then be merged with previous source
			//		- and validated that rule/modifier/callback/doc args in merged form are valid
			$meta = Merger::merge($meta, $sourceMeta);
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
