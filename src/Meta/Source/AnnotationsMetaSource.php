<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Source;

use Orisai\ReflectionMeta\Reader\AnnotationsMetaReader;

final class AnnotationsMetaSource extends ReflectorMetaSource
{

	public function __construct(?AnnotationsMetaReader $reader = null)
	{
		parent::__construct($reader ?? new AnnotationsMetaReader());
	}

}
