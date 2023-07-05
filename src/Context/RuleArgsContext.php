<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\MetaResolver;
use ReflectionProperty;

final class RuleArgsContext extends BaseArgsContext
{

	private MetaLoader $metaLoader;

	public function __construct(
		ReflectionProperty $property,
		MetaLoader $metaLoader,
		MetaResolver $metaResolver
	)
	{
		parent::__construct($property->getDeclaringClass(), $metaResolver);
		$this->metaLoader = $metaLoader;
	}

	public function getMetaLoader(): MetaLoader
	{
		return $this->metaLoader;
	}

}
