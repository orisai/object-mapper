<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Runtime\PhpMethodMeta;

final class AfterMappingCallbackArgs implements Args
{

	public PhpMethodMeta $meta;

	public function __construct(PhpMethodMeta $meta)
	{
		$this->meta = $meta;
	}

}
