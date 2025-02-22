<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Meta\Runtime\PhpMethodMeta;

final class ValidationCallbackArgs implements Args
{

	public CallbackRuntime $runtime;

	public PhpMethodMeta $meta;

	public function __construct(CallbackRuntime $runtime, PhpMethodMeta $meta)
	{
		$this->runtime = $runtime;
		$this->meta = $meta;
	}

}
