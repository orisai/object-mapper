<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks;

interface CallbackRuntime
{

	public const
		ProcessWithoutMapping = 'processWithoutMapping',
		Process = 'process',
		Always = 'always';

}
