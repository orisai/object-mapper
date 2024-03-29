<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

interface CallbackRuntime
{

	public const
		PROCESSING = 'processing',
		INITIALIZATION = 'initialization',
		ALWAYS = 'always';

}
