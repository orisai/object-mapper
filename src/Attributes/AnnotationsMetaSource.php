<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Attributes;

use Orisai\ReflectionMeta\Reader\AnnotationsMetaReader;

final class AnnotationsMetaSource extends BaseMetaSource
{

	public function __construct(?AnnotationsMetaReader $reader = null)
	{
		parent::__construct($reader ?? new AnnotationsMetaReader());
	}

}
