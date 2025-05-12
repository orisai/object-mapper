<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Meta\Runtime;

use Orisai\ObjectMapper\Args\Args;

/**
 * @readonly
 *
 * @internal
 */
interface NodeRuntimeMeta
{

	/**
	 * @return list<CallbackRuntimeMeta<Args>>
	 */
	public function getBeforeValidationCallbacks(): array;

	/**
	 * @return list<CallbackRuntimeMeta<Args>>
	 */
	public function getAfterValidationCallbacks(): array;

}
