<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes;

use Orisai\ReflectionMeta\Reader\AttributesMetaReader;

final class AttributesMetaSource extends BaseMetaSource
{

	public function __construct(?AttributesMetaReader $reader = null)
	{
		parent::__construct($reader ?? new AttributesMetaReader());
	}

}
