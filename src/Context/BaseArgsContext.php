<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Context;

use Orisai\ObjectMapper\Meta\MetaResolver;

/**
 * @internal
 */
abstract class BaseArgsContext
{

	private MetaResolver $metaResolver;

	public function __construct(MetaResolver $metaResolver)
	{
		$this->metaResolver = $metaResolver;
	}

	public function getMetaResolver(): MetaResolver
	{
		return $this->metaResolver;
	}

}
