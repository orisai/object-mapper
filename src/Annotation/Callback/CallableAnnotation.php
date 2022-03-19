<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Callback;

use Orisai\ObjectMapper\Annotation\BaseAnnotation;
use Orisai\ObjectMapper\Args\Args;
use Orisai\ObjectMapper\Callbacks\Callback;

/**
 * Base interface for callable annotations
 */
interface CallableAnnotation extends BaseAnnotation
{

	/**
	 * @return class-string<Callback<Args>>
	 */
	public function getType(): string;

}
