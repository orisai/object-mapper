<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Bridge\NetteDI;

use Nette\DI\Container;
use Orisai\ObjectMapper\Meta\MetaSource;
use Orisai\ObjectMapper\Meta\MetaSourceManager;
use function assert;

class LazyMetaSourceManager implements MetaSourceManager
{

	protected Container $container;

	/** @var array<string> */
	protected array $services = [];

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function addLazySource(string $serviceName): void
	{
		$this->services[] = $serviceName;
	}

	/**
	 * @return array<MetaSource>
	 */
	public function getAll(): array
	{
		$instances = [];

		foreach ($this->services as $service) {
			$instance = $this->container->getService($service);
			assert($instance instanceof MetaSource);
			$instances[] = $instance;
		}

		return $instances;
	}

}
