<?php declare(strict_types = 1);

namespace Orisai\ObjectMapper\Annotation\Callback;

use Doctrine\Common\Annotations\Annotation\Target;
use Orisai\ObjectMapper\Annotation\AutoMappedAnnotation;
use Orisai\ObjectMapper\Callbacks\BeforeCallback;

/**
 * @Annotation
 * @Target({"CLASS", "PROPERTY"})
 * @property-write string $method
 * @property-write string $runtime
 */
final class Before implements CallableAnnotation
{

	use AutoMappedAnnotation;

	protected function getMainProperty(): ?string
	{
		return 'method';
	}

	public function getType(): string
	{
		return BeforeCallback::class;
	}

}
