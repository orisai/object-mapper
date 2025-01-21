<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Callbacks\Context;

use Orisai\ObjectMapper\MappedObject;
use Orisai\ObjectMapper\Processing\Context\DynamicContext;
use Orisai\ObjectMapper\Processing\Context\ProcessorCallContext;
use Orisai\ObjectMapper\Processing\Context\ServicesContext;
use Orisai\ObjectMapper\Types\MappedObjectType;

final class ObjectContext extends CallbackBaseContext
{

	/** @var ProcessorCallContext<MappedObject> */
	private ProcessorCallContext $call;

	/**
	 * @param ProcessorCallContext<MappedObject> $call
	 */
	public function __construct(
		ServicesContext $services,
		DynamicContext $dynamic,
		ProcessorCallContext $call
	)
	{
		parent::__construct($services, $dynamic);
		$this->call = $call;
	}

	public function getType(): MappedObjectType
	{
		return $this->call->getType();
	}

}
