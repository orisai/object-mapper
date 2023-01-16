<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes;

use Orisai\ObjectMapper\ReflectionMeta\Collector\AttributesCollector;

final class AttributesMetaSource extends BaseMetaSource
{

	public function __construct(?AttributesCollector $collector = null)
	{
		parent::__construct($collector ?? new AttributesCollector());
	}

}
