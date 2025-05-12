<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Context;

use Orisai\ObjectMapper\Meta\MetaLoader;
use Orisai\ObjectMapper\Meta\RuntimeResolver;

class MetaContext
{

	private MetaLoader $metaLoader;

	private RuntimeResolver $metaResolver;

	public function __construct(MetaLoader $metaLoader, RuntimeResolver $metaResolver)
	{
		$this->metaLoader = $metaLoader;
		$this->metaResolver = $metaResolver;
	}

	public function getMetaLoader(): MetaLoader
	{
		return $this->metaLoader;
	}

	public function getMetaResolver(): RuntimeResolver
	{
		return $this->metaResolver;
	}

}
