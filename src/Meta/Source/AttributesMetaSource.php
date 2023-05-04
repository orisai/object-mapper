<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Source;

use Orisai\ReflectionMeta\Reader\AttributesMetaReader;

final class AttributesMetaSource extends ReflectorMetaSource
{

	public function __construct(?AttributesMetaReader $reader = null)
	{
		parent::__construct($reader ?? new AttributesMetaReader());
	}

}
