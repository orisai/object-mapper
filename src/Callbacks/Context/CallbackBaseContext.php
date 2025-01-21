<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks\Context;

use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Processing\Options;
use Orisai\ObjectMapper\Processing\Processor;
use Orisai\ObjectMapper\Types\Type;

abstract class CallbackBaseContext
{

	private ServicesContext $services;

	private DynamicContext $dynamic;

	public function __construct(
		ServicesContext $services,
		DynamicContext $dynamic
	)
	{
		$this->services = $services;
		$this->dynamic = $dynamic;
	}

	abstract public function getType(): Type;

	public function getProcessor(): Processor
	{
		return $this->services->getProcessor();
	}

	public function getOptions(): Options
	{
		return $this->dynamic->getOptions();
	}

	public function shouldInitializeObjects(): bool
	{
		return $this->dynamic->shouldInitializeObjects();
	}

}
