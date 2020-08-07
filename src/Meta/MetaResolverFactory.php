<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta;

interface MetaResolverFactory
{

	public function create(MetaLoader $loader): MetaResolver;

}
