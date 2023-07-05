<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\MetaResolver;

final class ResolverArgsContext
{

	private MetaLoader $metaLoader;

	private MetaResolver $metaResolver;

	public function __construct(MetaLoader $metaLoader, MetaResolver $metaResolver)
	{
		$this->metaLoader = $metaLoader;
		$this->metaResolver = $metaResolver;
	}

	public function getMetaLoader(): MetaLoader
	{
		return $this->metaLoader;
	}

	public function getMetaResolver(): MetaResolver
	{
		return $this->metaResolver;
	}

}
